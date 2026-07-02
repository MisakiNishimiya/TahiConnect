<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="description" content="TahiConnect — AI-Powered Tailoring Service Management with Virtual Try-On, Real-Time Order Tracking, and Digital Appointments." />
<meta name="theme-color" content="#2F5D50" />

<title>{{ $title ?? 'TahiConnect' }} — AI-Powered Tailoring</title>

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700,800|inter:300,400,500,600,700" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance

<style>
    /* Global Livewire loading progress bar */
    #nprogress-bar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(to right, #2F5D50, #4f9383, #D6B98C);
        z-index: 9999;
        transform-origin: left;
        transform: scaleX(0);
        transition: transform 0.2s ease, opacity 0.4s ease;
        border-radius: 0 2px 2px 0;
    }
    #nprogress-bar.loading {
        transform: scaleX(0.7);
        opacity: 1;
    }
    #nprogress-bar.done {
        transform: scaleX(1);
        opacity: 0;
    }
    /* Livewire wire:loading overlay helper */
    [wire\:loading] { display: none; }
    [wire\:loading][wire\:loading] { display: block; }
</style>
