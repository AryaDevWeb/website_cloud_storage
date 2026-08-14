<?php

namespace App\Livewire\Forms;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Form;

class RegisterForm extends Form
{
    public string $name     = '';
    public string $email    = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $role     = 'siswa';
    public string $nisn     = '';
    public string $nip      = '';

    /**
     * Dynamic validation rules based on selected role.
     * ADMIN is never an allowed option from public registration.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'role'     => ['required', Rule::in(['siswa', 'guru'])],
        ];

        if ($this->role === 'siswa') {
            $rules['nisn'] = [
                'required',
                'string',
                'max:20',
                Rule::unique('student_profiles', 'nisn'),
            ];
        } else {
            $rules['nip'] = [
                'required',
                'string',
                'max:20',
                Rule::unique('teacher_profiles', 'nip'),
            ];
        }

        return $rules;
    }

    /**
     * Return a plain array suitable for passing to RegisterUser::execute().
     *
     * @return array<string, string>
     */
    public function toData(): array
    {
        return [
            'name'     => $this->name,
            'email'    => $this->email,
            'password' => $this->password,
            'role'     => $this->role,
            'nisn'     => $this->nisn,
            'nip'      => $this->nip,
        ];
    }
}
