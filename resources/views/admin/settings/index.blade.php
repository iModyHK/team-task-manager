@extends('layouts.app')

@section('title', 'System Settings')

@section('header', 'System Settings')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- General Settings -->
    <div class="bg-white shadow rounded-lg">
        <div class="p-6 space-y-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900">
                    General Settings
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    Configure basic system settings and defaults.
                </p>
            </div>

            <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Site Name -->
                    <div>
                        <label for="site_name" class="block text-sm font-medium text-gray-700">
                            Site Name
                        </label>
                        <div class="mt-1">
                            <input type="text" 
                                   name="site_name" 
                                   id="site_name" 
                                   value="{{ old('site_name', $settings['site_name']) }}"
                                   class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                        @error('site_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Default Timezone -->
                    <div>
                        <label for="timezone" class="block text-sm font-medium text-gray-700">
                            Default Timezone
                        </label>
                        <div class="mt-1">
                            <select id="timezone" 
                                    name="timezone" 
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                @foreach($timezones as $tz)
                                    <option value="{{ $tz }}" {{ old('timezone', $settings['timezone']) === $tz ? 'selected' : '' }}>
                                        {{ $tz }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('timezone')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Security Settings -->
    <div class="bg-white shadow rounded-lg">
        <div class="p-6 space-y-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900">
                    Security Settings
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    Configure system-wide security settings.
                </p>
            </div>

            <form action="{{ route('admin.settings.security.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Password Requirements -->
                <div>
                    <h4 class="text-sm font-medium text-gray-900">Password Requirements</h4>
                    <div class="mt-4 space-y-4">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="checkbox" 
                                       name="require_strong_password" 
                                       id="require_strong_password"
                                       {{ old('require_strong_password', $settings['require_strong_password']) ? 'checked' : '' }}
                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="require_strong_password" class="font-medium text-gray-700">
                                    Require Strong Passwords
                                </label>
                                <p class="text-gray-500">Enforce minimum password requirements (8 characters, uppercase, lowercase, numbers, special characters)</p>
                            </div>
                        </div>

                        <div>
                            <label for="password_expiry_days" class="block text-sm font-medium text-gray-700">
                                Password Expiry (Days)
                            </label>
                            <div class="mt-1">
                                <input type="number" 
                                       name="password_expiry_days" 
                                       id="password_expiry_days" 
                                       value="{{ old('password_expiry_days', $settings['password_expiry_days']) }}"
                                       min="0"
                                       class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Set to 0 to disable password expiry</p>
                        </div>
                    </div>
                </div>

                <!-- Two-Factor Authentication -->
                <div>
                    <h4 class="text-sm font-medium text-gray-900">Two-Factor Authentication</h4>
                    <div class="mt-4 space-y-4">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="checkbox" 
                                       name="require_2fa" 
                                       id="require_2fa"
                                       {{ old('require_2fa', $settings['require_2fa']) ? 'checked' : '' }}
                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="require_2fa" class="font-medium text-gray-700">
                                    Require Two-Factor Authentication
                                </label>
                                <p class="text-gray-500">All users will be required to set up 2FA</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="checkbox" 
                                       name="allow_remember_2fa" 
                                       id="allow_remember_2fa"
                                       {{ old('allow_remember_2fa', $settings['allow_remember_2fa']) ? 'checked' : '' }}
                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="allow_remember_2fa" class="font-medium text-gray-700">
                                    Allow "Remember Device"
                                </label>
                                <p class="text-gray-500">Users can skip 2FA on trusted devices</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Session Settings -->
                <div>
                    <h4 class="text-sm font-medium text-gray-900">Session Settings</h4>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label for="session_lifetime" class="block text-sm font-medium text-gray-700">
                                Session Lifetime (Minutes)
                            </label>
                            <div class="mt-1">
                                <input type="number" 
                                       name="session_lifetime" 
                                       id="session_lifetime" 
                                       value="{{ old('session_lifetime', $settings['session_lifetime']) }}"
                                       min="1"
                                       class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="checkbox" 
                                       name="force_ssl" 
                                       id="force_ssl"
                                       {{ old('force_ssl', $settings['force_ssl']) ? 'checked' : '' }}
                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="force_ssl" class="font-medium text-gray-700">
                                    Force SSL
                                </label>
                                <p class="text-gray-500">Require HTTPS for all connections</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        Save Security Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Email Settings -->
    <div class="bg-white shadow rounded-lg">
        <div class="p-6 space-y-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900">
                    Email Settings
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    Configure email server and notification settings.
                </p>
            </div>

            <form action="{{ route('admin.settings.email.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Mail Driver -->
                    <div>
                        <label for="mail_driver" class="block text-sm font-medium text-gray-700">
                            Mail Driver
                        </label>
                        <div class="mt-1">
                            <select id="mail_driver" 
                                    name="mail_driver" 
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="smtp" {{ old('mail_driver', $settings['mail_driver']) === 'smtp' ? 'selected' : '' }}>SMTP</option>
                                <option value="mailgun" {{ old('mail_driver', $settings['mail_driver']) === 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                                <option value="ses" {{ old('mail_driver', $settings['mail_driver']) === 'ses' ? 'selected' : '' }}>Amazon SES</option>
                            </select>
                        </div>
                    </div>

                    <!-- From Address -->
                    <div>
                        <label for="mail_from_address" class="block text-sm font-medium text-gray-700">
                            From Address
                        </label>
                        <div class="mt-1">
                            <input type="email" 
                                   name="mail_from_address" 
                                   id="mail_from_address" 
                                   value="{{ old('mail_from_address', $settings['mail_from_address']) }}"
                                   class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>

                    <!-- From Name -->
                    <div>
                        <label for="mail_from_name" class="block text-sm font-medium text-gray-700">
                            From Name
                        </label>
                        <div class="mt-1">
                            <input type="text" 
                                   name="mail_from_name" 
                                   id="mail_from_name" 
                                   value="{{ old('mail_from_name', $settings['mail_from_name']) }}"
                                   class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                </div>

                <!-- SMTP Settings -->
                <div x-data="{ driver: '{{ old('mail_driver', $settings['mail_driver']) }}' }" 
                     x-show="driver === 'smtp'"
                     class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="smtp_host" class="block text-sm font-medium text-gray-700">
                                SMTP Host
                            </label>
                            <div class="mt-1">
                                <input type="text" 
                                       name="smtp_host" 
                                       id="smtp_host" 
                                       value="{{ old('smtp_host', $settings['smtp_host']) }}"
                                       class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>

                        <div>
                            <label for="smtp_port" class="block text-sm font-medium text-gray-700">
                                SMTP Port
                            </label>
                            <div class="mt-1">
                                <input type="number" 
                                       name="smtp_port" 
                                       id="smtp_port" 
                                       value="{{ old('smtp_port', $settings['smtp_port']) }}"
                                       class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>

                        <div>
                            <label for="smtp_username" class="block text-sm font-medium text-gray-700">
                                SMTP Username
                            </label>
                            <div class="mt-1">
                                <input type="text" 
                                       name="smtp_username" 
                                       id="smtp_username" 
                                       value="{{ old('smtp_username', $settings['smtp_username']) }}"
                                       class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>

                        <div>
                            <label for="smtp_password" class="block text-sm font-medium text-gray-700">
                                SMTP Password
                            </label>
                            <div class="mt-1">
                                <input type="password" 
                                       name="smtp_password" 
                                       id="smtp_password" 
                                       value="{{ old('smtp_password', $settings['smtp_password']) }}"
                                       class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            onclick="testEmailSettings()"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Test Connection
                    </button>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        Save Email Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    async function testEmailSettings() {
        try {
            const response = await fetch('{{ route("admin.settings.email.test") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    mail_driver: document.getElementById('mail_driver').value,
                    mail_from_address: document.getElementById('mail_from_address').value,
                    mail_from_name: document.getElementById('mail_from_name').value,
                    smtp_host: document.getElementById('smtp_host').value,
                    smtp_port: document.getElementById('smtp_port').value,
                    smtp_username: document.getElementById('smtp_username').value,
                    smtp_password: document.getElementById('smtp_password').value,
                })
            });

            const data = await response.json();

            if (response.ok) {
                alert('Email settings test successful!');
            } else {
                alert('Email settings test failed: ' + data.message);
            }
        } catch (error) {
            alert('Error testing email settings: ' + error.message);
        }
    }
</script>
@endpush
