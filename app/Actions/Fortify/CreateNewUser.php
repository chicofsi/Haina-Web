<?php

namespace App\Actions\Fortify;

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array  $input
     * @return \App\Models\Admin
     */
    public function create(array $input)
    {
        Validator::make($input, [
            'fullname' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'password' => $this->passwordRules(),
        ])->validate();

        return Admin::create([
            'fullname' => $input['fullname'],
            'username' => $input['username'],
            'role' => $input['role'],
            'password' => Hash::make($input['password']),
        ]);
    }
}
