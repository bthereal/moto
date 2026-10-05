<nav class="-mx-3 flex flex-1 justify-end">
    @auth
        <a
            href="{{ url('/dashboard') }}"
            class="rounded-md px-3 py-2 text-sm font-semibold uppercase tracking-wide text-white/80 ring-1 ring-transparent transition hover:text-white focus:outline-none focus-visible:ring-[#E10600]"
        >
            Dashboard
        </a>
    @else
        <a
            href="{{ route('login') }}"
            class="rounded-md px-3 py-2 text-sm font-semibold uppercase tracking-wide text-white/80 ring-1 ring-transparent transition hover:text-white focus:outline-none focus-visible:ring-[#E10600]"
        >
            Log in
        </a>

        @if (Route::has('register'))
            <a
                href="{{ route('register') }}"
                class="rounded-md px-3 py-2 text-sm font-semibold uppercase tracking-wide text-white/80 ring-1 ring-transparent transition hover:text-white focus:outline-none focus-visible:ring-[#E10600]"
            >
                Register
            </a>
        @endif
    @endauth
</nav>
