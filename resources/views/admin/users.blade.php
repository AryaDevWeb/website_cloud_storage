@extends('layouts.app')

@section('title', 'User Access')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">User Access</h1>
    <p class="text-sm text-gray-500 mt-1">Assign Google users to RPL classes, roles, and storage quotas.</p>
</div>

<div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">User</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Role</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Class</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Major</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Quota</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-600">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($users as $user)
                    <tr>
                        <form method="POST" action="/admin/users/{{ $user->id }}">
                            @csrf
                            @method('PATCH')
                            <td class="px-4 py-3 align-top">
                                <div class="font-semibold text-gray-900">{{ $user->name }}</div>
                                <div class="text-xs text-gray-500">{{ $user->email }}</div>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <select name="role" class="w-36 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
                                    @foreach($roles as $role)
                                        <option value="{{ $role }}" @selected($user->role === $role)>{{ $role }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <select name="target_kelas" class="w-32 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
                                    <option value="">None</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class }}" @selected($user->target_kelas === $class)>{{ $class }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <select name="target_jurusan" class="w-24 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
                                    <option value="">None</option>
                                    @foreach($majors as $major)
                                        <option value="{{ $major }}" @selected($user->target_jurusan === $major)>{{ $major }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <input name="storage_limit_gb" type="number" min="1" max="500"
                                    value="{{ max(1, (int) round(($user->storage_limit_bytes ?: 0) / 1024 / 1024 / 1024)) }}"
                                    class="w-24 rounded-lg border border-gray-200 px-3 py-2 text-sm">
                                <span class="text-xs text-gray-500">GB</span>
                            </td>
                            <td class="px-4 py-3 text-right align-top">
                                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Save</button>
                            </td>
                        </form>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
