<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Login extends Component
{
    public string $username = '';
    public string $password = '';
    public string $error    = '';
    public bool   $showPassword = false;

    public function login(): void
    {
        $this->error = '';

        $this->username = trim($this->username);

        if (empty($this->username) || empty($this->password)) {
            $this->error = 'Please enter your username and password.';
            return;
        }

        $user = User::where('username', $this->username)->first();

        if (!$user || !Hash::check($this->password, $user->password)) {
            $this->error = 'Invalid username or password.';
            $this->password = '';
            return;
        }

        Auth::login($user, remember: true);

        $this->redirect('/', navigate: false);
    }

    public function render()
    {
        return view('livewire.login')
            ->layout('layouts.guest');
    }
}
