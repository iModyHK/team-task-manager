@extends('layouts.guest')

@section('title', 'Verify Email')

@section('content')
<div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-gray-50">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <!-- Logo -->
        <div class="flex justify-center">
            <div class="text-center">
                <i class="fas fa-tasks text-4xl text-indigo-600"></i>
                <h2 class="mt-2 text-3xl font-bold text-gray-900">
                    {{ config('app.name') }}
                </h2>
            </div>
        </div>

        <h2 class="mt-6 text-center text-2xl font-semibold text-gray-900">
            Verify your email address
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            You're almost there! Just verify your email to complete your registration
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            @if (session('status') == 'verification-link-sent')
                <div class="mb-4 p-4 rounded-md bg-green-50">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">
                                A new verification link has been sent to your email address.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="text-center">
                <p class="text-sm text-gray-600">
                    Before proceeding, please check your email for a verification link. 
                    If you didn't receive the email, click the button below to request another.
                </p>

                <form class="mt-4" method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" 
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Resend Verification Email
                    </button>
                </form>
            </div>

            <!-- Email Tips -->
            <div class="mt-6 rounded-md bg-blue-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">
                            Can't find the email?
                        </h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <ul class="list-disc pl-5 space-y-1">
                                <li>Check your spam or junk folder</li>
                                <li>Make sure to add us to your safe senders list</li>
                                <li>Check if you entered the correct email address</li>
                                <li>Allow a few minutes for the email to arrive</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

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
