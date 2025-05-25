@extends('layouts.guest')

@section('title', 'Confirm Password')

@section('content')
<div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-gray-50">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <!-- Logo -->
        <div class="flex justify-center">
            <div class="text-center">
                <i class="fas fa-lock text-4xl text-indigo-600"></i>
                <h2 class="mt-2 text-3xl font-bold text-gray-900">
                    Security Check
                </h2>
            </div>
        </div>

        <h2 class="mt-6 text-center text-2xl font-semibold text-gray-900">
            Confirm your password
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            This is a secure area. Please confirm your password before continuing.
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <form class="space-y-6" action="{{ route('password.confirm') }}" method="POST">
                @csrf

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        Password
                    </label>
                    <div class="mt-1 relative" x-data="{ show: false }">
                        <input id="password" 
                               name="password" 
                               :type="show ? 'text' : 'password'"
                               required
                               autocomplete="current-password"
                               class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('password') border-red-500 @enderror">
                        <button type="button" 
                                @click="show = !show"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <button type="submit" 
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Confirm Password
                    </button>
                </div>
            </form>

            <!-- Security Notice -->
            <div class="mt-6">
                <div class="rounded-md bg-blue-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-shield-alt text-blue-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">
                                Why is this required?
                            </h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p>
                                    We ask for your password to protect your account and ensure that only you can access sensitive features and information.
                                </p>
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
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" 
                           class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                            Forgot your password?
                        </a>
                    @endif
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
