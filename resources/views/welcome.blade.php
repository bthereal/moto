<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Moto') }} — Formula 1 Team Management</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600,700,800&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased font-sans bg-[#0B0B0F] text-white">
        <div class="relative min-h-screen overflow-hidden">
            <div class="h-2 w-full shrink-0" style="background-image: repeating-linear-gradient(45deg, #E10600 0 20px, #f5f5f5 20px 40px);"></div>

            <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[640px]" style="background: radial-gradient(60% 55% at 50% 0%, rgba(225,6,0,0.28), transparent 70%);"></div>

            <div class="relative mx-auto w-full max-w-7xl px-6 lg:px-8">
                <header class="flex items-center justify-between py-8">
                    <div class="flex items-center gap-3">
                        <x-application-logo class="h-8 w-auto fill-current text-[#E10600]" />
                        <span class="text-lg font-extrabold uppercase tracking-[0.2em]">{{ config('app.name', 'Moto') }}</span>
                    </div>

                    @if (Route::has('login'))
                        <livewire:welcome.navigation />
                    @endif
                </header>

                <main class="pb-24 pt-8">
                    <div class="max-w-3xl">
                        <p class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-white/60">
                            Formula 1 Team Operations
                        </p>

                        <h1 class="mt-6 text-5xl font-extrabold leading-[1.05] tracking-tight sm:text-6xl">
                            Run the pit wall
                            <span class="block text-[#E10600]">like a championship team.</span>
                        </h1>

                        <p class="mt-6 max-w-xl text-lg text-white/60">
                            Track every constructor, car, and driver on the grid — and follow every part from <span class="text-white/80">required</span> to <span class="text-white/80">fitted</span> before a car is cleared to leave the garage.
                        </p>

                        <div class="mt-10 flex flex-wrap items-center gap-4">
                            @auth
                                <a
                                    href="{{ route('dashboard') }}"
                                    class="rounded-md bg-[#E10600] px-6 py-3 text-sm font-bold uppercase tracking-wide text-white transition hover:bg-[#c00500] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#E10600] focus-visible:ring-offset-2 focus-visible:ring-offset-[#0B0B0F]"
                                >
                                    Go to dashboard
                                </a>
                            @else
                                <a
                                    href="{{ route('login') }}"
                                    class="rounded-md bg-[#E10600] px-6 py-3 text-sm font-bold uppercase tracking-wide text-white transition hover:bg-[#c00500] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#E10600] focus-visible:ring-offset-2 focus-visible:ring-offset-[#0B0B0F]"
                                >
                                    Sign in
                                </a>

                                @if (Route::has('register'))
                                    <a
                                        href="{{ route('register') }}"
                                        class="rounded-md border border-white/20 px-6 py-3 text-sm font-bold uppercase tracking-wide text-white/90 transition hover:bg-white/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/40 focus-visible:ring-offset-2 focus-visible:ring-offset-[#0B0B0F]"
                                    >
                                        Create an account
                                    </a>
                                @endif
                            @endauth
                        </div>
                    </div>

                    <div class="mt-20 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-lg border border-white/10 bg-white/[0.03] p-6">
                            <div class="flex size-12 items-center justify-center rounded-full bg-[#E10600]/10">
                                <svg class="size-6 text-[#E10600]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <circle cx="12" cy="8" r="4" />
                                    <path stroke-linecap="round" d="M4 20c0-4.4 3.6-8 8-8s8 3.6 8 8" />
                                </svg>
                            </div>
                            <h2 class="mt-6 text-lg font-bold text-white">Team management</h2>
                            <p class="mt-2 text-sm leading-relaxed text-white/50">
                                Every constructor's base, principal, founding year, and full staff roster — engineers and drivers alike.
                            </p>
                        </div>

                        <div class="rounded-lg border border-white/10 bg-white/[0.03] p-6">
                            <div class="flex size-12 items-center justify-center rounded-full bg-[#E10600]/10">
                                <svg class="size-6 text-[#E10600]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v18" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 4c2-1.3 4-1.3 6 0s4 1.3 6 0v9c-2 1.3-4 1.3-6 0s-4-1.3-6 0V4Z" />
                                </svg>
                            </div>
                            <h2 class="mt-6 text-lg font-bold text-white">Vehicle &amp; race status</h2>
                            <p class="mt-2 text-sm leading-relaxed text-white/50">
                                Move a car through active, testing, and retired status, with driver assignments and per-season chassis records.
                            </p>
                        </div>

                        <div class="rounded-lg border border-white/10 bg-white/[0.03] p-6">
                            <div class="flex size-12 items-center justify-center rounded-full bg-[#E10600]/10">
                                <svg class="size-6 text-[#E10600]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <circle cx="6" cy="18" r="3" />
                                    <circle cx="18" cy="6" r="3" />
                                    <path stroke-linecap="round" d="M8.5 15.5l7-7" />
                                </svg>
                            </div>
                            <h2 class="mt-6 text-lg font-bold text-white">Parts pipeline</h2>
                            <p class="mt-2 text-sm leading-relaxed text-white/50">
                                Required, in-transit, delivered, fitted — a car can't return to active until every part on order is fitted.
                            </p>
                        </div>

                        <div class="rounded-lg border border-white/10 bg-white/[0.03] p-6">
                            <div class="flex size-12 items-center justify-center rounded-full bg-[#E10600]/10">
                                <svg class="size-6 text-[#E10600]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 6L2 12l6 6M16 6l6 6-6 6" />
                                </svg>
                            </div>
                            <h2 class="mt-6 text-lg font-bold text-white">REST API access</h2>
                            <p class="mt-2 text-sm leading-relaxed text-white/50">
                                A JWT-secured REST API mirrors every screen in the app, ready for integration with other systems.
                            </p>
                        </div>
                    </div>

                    <div class="mt-16 rounded-lg border border-white/10 bg-white/[0.03] p-6">
                        <h3 class="text-xs font-semibold uppercase tracking-[0.2em] text-white/40">The parts lifecycle</h3>
                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-amber-500/15 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-300">Required</span>
                            <svg class="size-4 text-white/20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                            <span class="rounded-full bg-blue-500/15 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-blue-300">In-transit</span>
                            <svg class="size-4 text-white/20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                            <span class="rounded-full bg-indigo-500/15 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-indigo-300">Delivered</span>
                            <svg class="size-4 text-white/20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                            <span class="rounded-full bg-green-500/15 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-green-300">Fitted</span>
                            <span class="ml-2 text-sm text-white/40">— only then can the car go back to active.</span>
                        </div>
                    </div>
                </main>

                <footer class="border-t border-white/10 py-10 text-center text-xs uppercase tracking-[0.2em] text-white/30">
                    {{ config('app.name', 'Moto') }} &middot; Formula 1 Team Management
                </footer>
            </div>

            <div class="h-2 w-full shrink-0" style="background-image: repeating-linear-gradient(45deg, #E10600 0 20px, #f5f5f5 20px 40px);"></div>
        </div>
    </body>
</html>
