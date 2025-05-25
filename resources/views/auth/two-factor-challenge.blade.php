@extends('layouts.guest')

@section('title', 'Two-Factor Challenge')

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
            Security verification required
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            Enter the authentication code from your authenticator app
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <div x-data="{ mode: 'code' }">
                <!-- Mode Selector -->
                <div class="flex justify-center space-x-4 mb-6">
                    <button @click="mode = 'code'" 
                            :class="{ 'bg-indigo-50 text-indigo-600': mode === 'code', 'text-gray-500 hover:text-gray-700': mode !== 'code' }"
                            class="px-4 py-2 text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Authentication Code
                    </button>
                    <button @click="mode = 'recovery'" 
                            :class="{ 'bg-indigo-50 text-indigo-600': mode === 'recovery', 'text-gray-500 hover:text-gray-700': mode !== 'recovery' }"
                            class="px-4 py-2 text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Recovery Code
                    </button>
                </div>

                <!-- Authentication Code Form -->
                <form method="POST" 
                      action="{{ route('2fa.verify') }}" 
                      x-show="mode === 'code'"
                      class="space-y-6">
                    @csrf

                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700">
                            Authentication Code
                        </label>
                        <div class="mt-1">
                            <input id="code" 
                                   name="code" 
                                   type="text"
                                   inputmode="numeric"
                                   pattern="[0-9]*"
                                   autocomplete="one-time-code"
                                   required
                                   autofocus
                                   class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('code') border-red-500 @enderror">
                        </div>
                        @error('code')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <button type="submit" 
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Verify
                        </button>
                    </div>
                </form>

                <!-- Recovery Code Form -->
                <form method="POST" 
                      action="{{ route('2fa.verify-recovery') }}" 
                      x-show="mode === 'recovery'"
                      class="space-y-6">
                    @csrf

                    <div>
                        <label for="recovery_code" class="block text-sm font-medium text-gray-700">
                            Recovery Code
                        </label>
                        <div class="mt-1">
                            <input id="recovery_code" 
                                   name="recovery_code" 
                                   type="text"
                                   required
                                   autocomplete="off"
                                   class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('recovery_code') border-red-500 @enderror">
                        </div>
                        @error('recovery_code')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <button type="submit" 
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Verify Recovery Code
                        </button>
                    </div>
                </form>
            </div>

            <!-- Help Text -->
            <div class="mt-6">
                <div class="rounded-md bg-blue-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">
                                Having trouble?
                            </h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    <li>Make sure your device's time is correct</li>
                                    <li>Enter the code without spaces</li>
                                    <li>Codes expire after 30 seconds</li>
                                    <li>Use a recovery code if you can't access your device</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Actions -->
            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">
                            Need help?
                        </span>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-center space-x-4">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" 
                                class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                            Sign out
                        </button>
                    </form>
                    <span class="text-gray-400">|</span>
                    <a href="{{ route('contact') }}" 
                       class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                        Contact Support
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
