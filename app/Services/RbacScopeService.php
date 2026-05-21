<?php

namespace App\Services;

use App\Models\User;
use App\Models\Folder;
use App\Models\Gallery;

class RbacScopeService
{
    /**
     * Get all folder IDs that a user is allowed to access.
     */
    public static function getAccessibleFolderIds(User $user): array
    {
        if ($user->role === 'admin') {
            return Folder::pluck('id')->toArray();
        }

        // Start with folders owned by the user
        $ownedIds = Folder::where('user_id', $user->id)->pluck('id')->toArray();

        // Get root-level or nested folders that are shared drives matching the user's scope
        $sharedQuery = Folder::where('is_shared_drive', true);
        $sharedQuery->where(function($q) use ($user) {
            if ($user->target_kelas) {
                $q->orWhere('scope_kelas', $user->target_kelas);
            }
            if ($user->target_jurusan) {
                $q->orWhere('scope_jurusan', $user->target_jurusan);
            }
            if ($user->role === 'tendik') {
                $q->orWhere('scope_tendik', 'tendik');
            }
        });

        $sharedRootIds = $sharedQuery->pluck('id')->toArray();
        $allAccessibleIds = array_unique(array_merge($ownedIds, $sharedRootIds));

        // Recursively find all children/subfolders of the accessible folders
        $toProcess = $sharedRootIds;
        while (!empty($toProcess)) {
            $children = Folder::whereIn('parent_id', $toProcess)->pluck('id')->toArray();
            $newChildren = array_diff($children, $allAccessibleIds);
            if (empty($newChildren)) {
                break;
            }
            $allAccessibleIds = array_merge($allAccessibleIds, $newChildren);
            $toProcess = $newChildren;
        }

        return $allAccessibleIds;
    }

    /**
     * Get the root folders query builder visible to a user.
     */
    public static function getRootFolders(User $user)
    {
        $query = Folder::whereNull('parent_id');

        if ($user->role === 'admin') {
            return $query;
        }

        return $query->where(function($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere(function($sq) use ($user) {
                  $sq->where('is_shared_drive', true)
                     ->where(function($ssq) use ($user) {
                         if ($user->target_kelas) {
                             $ssq->orWhere('scope_kelas', $user->target_kelas);
                         }
                         if ($user->target_jurusan) {
                             $ssq->orWhere('scope_jurusan', $user->target_jurusan);
                         }
                         if ($user->role === 'tendik') {
                             $ssq->orWhere('scope_tendik', 'tendik');
                         }
                     });
              });
        });
    }

    /**
     * Determine if a user can access (view/read) a folder.
     */
    public static function canAccessFolder(User $user, Folder $folder): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        $current = $folder;
        while ($current) {
            if ($current->user_id === $user->id) {
                return true;
            }

            if ($current->is_shared_drive) {
                if ($user->role === 'guru_wali' && $current->scope_kelas === $user->target_kelas) {
                    return true;
                }
                if ($user->role === 'guru_jurusan' && $current->scope_jurusan === $user->target_jurusan) {
                    return true;
                }
                if ($user->role === 'tendik' && $current->scope_tendik === 'tendik') {
                    return true;
                }
                if ($user->role === 'siswa') {
                    if ($current->scope_kelas === $user->target_kelas || $current->scope_jurusan === $user->target_jurusan) {
                        return true;
                    }
                }
            }

            if (!$current->parent_id) {
                break;
            }

            // Using relationship or database query to climb up
            $current = $current->parent ?: Folder::find($current->parent_id);
        }

        return false;
    }

    /**
     * Determine if a user can write (create/upload/rename/delete) in a folder.
     */
    public static function canWriteFolder(User $user, Folder $folder): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        // Must at least be able to access the folder
        if (!self::canAccessFolder($user, $folder)) {
            return false;
        }

        $current = $folder;
        $isInsideSharedDrive = false;
        $sharedScopeMatch = false;
        $isInsideAssignment = false;

        while ($current) {
            if ($current->is_shared_drive) {
                $isInsideSharedDrive = true;
                if ($user->role === 'guru_wali' && $current->scope_kelas === $user->target_kelas) {
                    $sharedScopeMatch = true;
                }
                if ($user->role === 'guru_jurusan' && $current->scope_jurusan === $user->target_jurusan) {
                    $sharedScopeMatch = true;
                }
                if ($user->role === 'tendik' && $current->scope_tendik === 'tendik') {
                    $sharedScopeMatch = true;
                }
                if ($user->role === 'siswa') {
                    if ($current->scope_kelas === $user->target_kelas || $current->scope_jurusan === $user->target_jurusan) {
                        $sharedScopeMatch = true;
                    }
                }
            }

            if ($current->is_assignment_folder) {
                $isInsideAssignment = true;
            }

            if (!$current->parent_id) {
                break;
            }
            $current = $current->parent ?: Folder::find($current->parent_id);
        }

        if ($isInsideSharedDrive) {
            if (!$sharedScopeMatch) {
                return false;
            }

            // Siswa can only write inside assignment subfolders
            if ($user->role === 'siswa') {
                return $isInsideAssignment;
            }

            return true;
        }

        // For personal folders, check ownership
        return $folder->user_id === $user->id;
    }

    /**
     * Determine if a user can access a file.
     */
    public static function canAccessFile(User $user, Gallery $file): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($file->user_id === $user->id) {
            return true;
        }

        if ($file->izin === 1) {
            return true;
        }

        if ($file->folder_id) {
            $folder = $file->folder ?: Folder::find($file->folder_id);
            if ($folder) {
                return self::canAccessFolder($user, $folder);
            }
        }

        return false;
    }

    /**
     * Determine if a user can write/modify/delete a file.
     */
    public static function canWriteFile(User $user, Gallery $file): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        // Folder owner can write/delete files in their folder
        if ($file->folder_id) {
            $folder = $file->folder ?: Folder::find($file->folder_id);
            if ($folder) {
                if ($folder->user_id === $user->id) {
                    return true;
                }
                if (self::canWriteFolder($user, $folder)) {
                    // If user is a student, they can only write/delete their OWN files in assignment folders
                    if ($user->role === 'siswa') {
                        return $file->user_id === $user->id;
                    }
                    return true;
                }
            }
        }

        // Otherwise, must be the owner of the file
        return $file->user_id === $user->id;
    }
}
