<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
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

        // Fix #7: Rate limit by BOTH IP and username separately.
        // Keying only by IP fails behind proxies/load balancers where all users share one IP.
        // Keying only by username allows distributed brute-force from many IPs.
        // Together they cover both attack surfaces.
        $ipKey        = 'login_ip_'   . preg_replace('/[^a-zA-Z0-9]/', '_', request()->ip());
        $userKey      = 'login_user_' . preg_replace('/[^a-zA-Z0-9]/', '_', $this->username);
        $ipAttempts   = (int) Cache::store('file')->get($ipKey,   0);
        $userAttempts = (int) Cache::store('file')->get($userKey, 0);

        // IP cap: 10 attempts (covers multiple usernames from one IP)
        // Username cap: 5 attempts (covers distributed brute-force on one account)
        if ($ipAttempts >= 10 || $userAttempts >= 5) {
            $this->error = 'Too many login attempts. Please wait a minute before trying again.';
            return;
        }

        $user = User::where('username', $this->username)->first();

        if (!$user || !Hash::check($this->password, $user->password)) {
            Cache::store('file')->put($ipKey,   $ipAttempts   + 1, 60);
            Cache::store('file')->put($userKey, $userAttempts + 1, 60);
            $this->error = 'Invalid username or password.';
            $this->password = '';
            return;
        }

        // Reset both counters on successful login
        Cache::store('file')->forget($ipKey);
        Cache::store('file')->forget($userKey);
        Auth::login($user, remember: true);

        $this->redirect('/', navigate: false);
    }

    public function render()
    {
        return view('livewire.login')
            ->layout('layouts.guest');
    }
}
