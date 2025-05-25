@extends('layouts.guest')

@section('title', 'Two-Factor Authentication Setup')

@section('content')
<div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-gray-50">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <!-- Logo -->
        <div class="flex justify-center">
            <div class="text-center">
                <i class="fas fa-shield-alt text-4xl text-indigo-600"></i>
                <h2 class="mt-2 text-3xl font-bold text-gray-900">
                    Two-Factor Authentication
                </h2>
            </div>
        </div>

        <h2 class="mt-6 text-center text-2xl font-semibold text-gray-900">
            Set up additional security
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            Protect your account with an additional layer of security
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <!-- QR Code Section -->
            <div class="text-center mb-6">
                <p class="text-sm text-gray-600 mb-4">
                    Scan this QR code with your authenticator app
                </p>
                <div class="flex justify-center mb-4">
                    {!! $qrCode !!}
                </div>
                <div x-data="{ shown: false }" class="text-sm">
                    <button @click="shown = !shown" 
                            type="button"
                            class="text-indigo-600 hover:text-indigo-500">
                        Can't scan the QR code?
                    </button>
                    <div x-show="shown" 
                         x-transition
                         class="mt-2 p-2 bg-gray-50 rounded">
                        <p class="font-mono text-xs break-all">
                            {{ $secretKey }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Verification Form -->
            <form method="POST" action="{{ route('2fa.confirm') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700">
                        Verification Code
                    </label>
                    <div class="mt-1">
                        <input id="code" 
                               name="code" 
                               type="text"
                               inputmode="numeric"
                               pattern="[0-9]*"
                               autocomplete="one-time-code"
                               required
                               class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('code') border-red-500 @enderror">
                    </div>
                    @error('code')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <button type="submit" 
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Verify and Enable
                    </button>
                </div>
            </form>

            <!-- Recovery Codes -->
            @if(isset($recoveryCodes))
                <div class="mt-6">
                    <div class="rounded-md bg-yellow-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">
                                    Save your recovery codes
                                </h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p>
                                        Store these recovery codes in a secure location. They can be used to recover access to your account if you lose your 2FA device.
                                    </p>
                                    <div class="mt-3 font-mono text-xs bg-white p-2 rounded border border-yellow-200">
                                        @foreach($recoveryCodes as $code)
                                            <div>{{ $code }}</div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Setup Instructions -->
            <div class="mt-6">
                <div class="rounded-md bg-blue-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">
                                How to set up
                            </h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ol class="list-decimal pl-5 space-y-1">
                                    <li>Download an authenticator app (Google Authenticator, Authy, etc.)</li>
                                    <li>Scan the QR code with your authenticator app</li>
                                    <li>Enter the 6-digit code shown in your app</li>
                                    <li>Save your recovery codes somewhere safe</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cancel Setup -->
            <div class="mt-6 text-center">
                <form method="POST" action="{{ route('2fa.cancel') }}">
                    @csrf
                    <button type="submit" 
                            class="text-sm font-medium text-gray-600 hover:text-gray-500">
                        Skip for now
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
