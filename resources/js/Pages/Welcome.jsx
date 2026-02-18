import { Link, Head } from '@inertiajs/react';

export default function Welcome({ auth, canLogin, canRegister }) {
    return (
        <>
            <Head title="TicketBook - Event Ticket Booking" />

            <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
                {/* Navigation */}
                <nav className="fixed top-0 left-0 right-0 z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-200 dark:border-gray-800">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="flex justify-between items-center h-16">
                            <Link href="/" className="flex items-center space-x-2">
                                <svg
                                    viewBox="0 0 64 64"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                    className="h-8 w-auto"
                                >
                                    <defs>
                                        <linearGradient id="ticketGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" stopColor="#818cf8" />
                                            <stop offset="100%" stopColor="#6366f1" />
                                        </linearGradient>
                                    </defs>
                                    <rect x="4" y="16" width="56" height="32" rx="8" fill="url(#ticketGradient)" />
                                    <circle cx="4" cy="32" r="4" fill="#f3f4f6" />
                                    <circle cx="60" cy="32" r="4" fill="#f3f4f6" />
                                    <rect x="14" y="22" width="36" height="20" rx="4" fill="white" opacity="0.95" />
                                    <path d="M32 26l2 4h4l-3 2 1 4-4-2-4 2 1-4-3-2 4h4z" fill="#6366f1" />
                                </svg>
                                <span className="text-xl font-bold text-gray-900 dark:text-white">TicketBook</span>
                            </Link>

                            <div className="flex items-center space-x-4">
                                {canLogin && (
                                    <Link
                                        href="/login"
                                        className="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white font-medium transition-colors min-h-[44px] flex items-center px-3 py-2"
                                    >
                                        Sign In
                                    </Link>
                                )}
                                {canRegister && (
                                    <Link
                                        href="/register"
                                        className="bg-indigo-600 dark:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium hover:bg-indigo-700 dark:hover:bg-indigo-600 transition-all duration-200 hover:scale-105 min-h-[44px] flex items-center shadow-md hover:shadow-lg"
                                    >
                                        Get Started
                                    </Link>
                                )}
                            </div>
                        </div>
                    </div>
                </nav>

                {/* Hero Section */}
                <section className="pt-32 pb-20 px-4 sm:px-6 lg:px-8 overflow-hidden">
                    <div className="max-w-7xl mx-auto">
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                            <div className="text-center lg:text-left">
                                <h1 className="text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 dark:text-white leading-tight mb-6">
                                    Book Your Event Tickets{' '}
                                    <span className="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400">
                                        Effortlessly
                                    </span>
                                </h1>
                                <p className="text-lg sm:text-xl text-gray-600 dark:text-gray-300 mb-8 max-w-2xl mx-auto lg:mx-0">
                                    Discover amazing events and secure your spot in seconds. Simple, fast, and reliable ticket booking for all your favorite events.
                                </p>
                                <div className="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                                    <Link
                                        href="/register"
                                        className="bg-indigo-600 dark:bg-indigo-700 text-white px-8 py-4 rounded-xl font-semibold text-lg hover:bg-indigo-700 dark:hover:bg-indigo-600 transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl min-h-[44px] flex items-center justify-center"
                                    >
                                        Get Started
                                        <svg className="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                        </svg>
                                    </Link>
                                    <button
                                        onClick={() => document.getElementById('features').scrollIntoView({ behavior: 'smooth' })}
                                        className="bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-8 py-4 rounded-xl font-semibold text-lg border-2 border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-600 transition-all duration-200 min-h-[44px] flex items-center justify-center"
                                    >
                                        Learn More
                                    </button>
                                </div>
                            </div>
                            <div className="flex justify-center">
                                <svg
                                    viewBox="0 0 400 400"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                    className="w-full max-w-md"
                                >
                                    <defs>
                                        <linearGradient id="heroGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" stopColor="#818cf8" />
                                            <stop offset="100%" stopColor="#6366f1" />
                                        </linearGradient>
                                        <filter id="heroShadow" x="-50%" y="-50%" width="200%" height="200%">
                                            <feDropShadow dx="0" dy="20" stdDeviation="30" floodColor="#6366f1" floodOpacity="0.3"/>
                                        </filter>
                                    </defs>
                                    {/* Main ticket */}
                                    <rect x="50" y="100" width="300" height="200" rx="16" fill="url(#heroGradient)" filter="url(#heroShadow)" />
                                    {/* Side perforations */}
                                    <circle cx="50" cy="200" r="20" fill="#f3f4f6" />
                                    <circle cx="350" cy="200" r="20" fill="#f3f4f6" />
                                    {/* Inner ticket area */}
                                    <rect x="80" y="140" width="240" height="120" rx="12" fill="white" opacity="0.98" />
                                    {/* Ticket content */}
                                    <rect x="100" y="170" width="200" height="4" rx="2" fill="#6366f1" opacity="0.3" />
                                    <rect x="120" y="190" width="160" height="4" rx="2" fill="#6366f1" opacity="0.2" />
                                    <rect x="140" y="210" width="120" height="4" rx="2" fill="#6366f1" opacity="0.2" />
                                    {/* Decorative elements */}
                                    <circle cx="100" cy="160" r="8" fill="#6366f1" />
                                    <rect x="120" y="240" width="60" height="2" rx="1" fill="#6366f1" opacity="0.4" />
                                    <rect x="120" y="246" width="80" height="2" rx="1" fill="#6366f1" opacity="0.3" />
                                    {/* Star accent */}
                                    <path d="M200 160l6 12h12l-10 7 4 12-12-8-12 8 4-12-10-7h12z" fill="#6366f1" />
                                    {/* Floating elements */}
                                    <circle cx="80" cy="80" r="8" fill="#818cf8" opacity="0.6" />
                                    <circle cx="320" cy="320" r="12" fill="#818cf8" opacity="0.4" />
                                    <circle cx="100" cy="340" r="6" fill="#818cf8" opacity="0.5" />
                                    <rect x="340" y="100" width="30" height="30" rx="6" fill="#818cf8" opacity="0.3" transform="rotate(15 355 115)" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </section>

                {/* Features Section */}
                <section id="features" className="py-20 px-4 sm:px-6 lg:px-8 bg-white dark:bg-gray-800">
                    <div className="max-w-7xl mx-auto">
                        <div className="text-center mb-16">
                            <h2 className="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                                Why Choose TicketBook?
                            </h2>
                            <p className="text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                                Experience the easiest way to book event tickets with our platform designed for simplicity and reliability.
                            </p>
                        </div>

                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                            {/* Feature 1 */}
                            <div className="group p-6 bg-gray-50 dark:bg-gray-700/50 rounded-2xl border border-gray-200 dark:border-gray-600 hover:border-indigo-300 dark:hover:border-indigo-500 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                                <div className="w-14 h-14 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                                    <svg className="w-7 h-7 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                    </svg>
                                </div>
                                <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                    Easy Booking
                                </h3>
                                <p className="text-gray-600 dark:text-gray-300">
                                    Reserve tickets in just a few clicks with our streamlined booking process.
                                </p>
                            </div>

                            {/* Feature 2 */}
                            <div className="group p-6 bg-gray-50 dark:bg-gray-700/50 rounded-2xl border border-gray-200 dark:border-gray-600 hover:border-indigo-300 dark:hover:border-indigo-500 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                                <div className="w-14 h-14 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                                    <svg className="w-7 h-7 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                    Real-time Availability
                                </h3>
                                <p className="text-gray-600 dark:text-gray-300">
                                    See ticket availability updated instantly so you never miss an opportunity.
                                </p>
                            </div>

                            {/* Feature 3 */}
                            <div className="group p-6 bg-gray-50 dark:bg-gray-700/50 rounded-2xl border border-gray-200 dark:border-gray-600 hover:border-indigo-300 dark:hover:border-indigo-500 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                                <div className="w-14 h-14 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                                    <svg className="w-7 h-7 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                                <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                    Secure Transactions
                                </h3>
                                <p className="text-gray-600 dark:text-gray-300">
                                    Your data and payments are protected with enterprise-grade security.
                                </p>
                            </div>

                            {/* Feature 4 */}
                            <div className="group p-6 bg-gray-50 dark:bg-gray-700/50 rounded-2xl border border-gray-200 dark:border-gray-600 hover:border-indigo-300 dark:hover:border-indigo-500 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                                <div className="w-14 h-14 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                                    <svg className="w-7 h-7 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                    Mobile Friendly
                                </h3>
                                <p className="text-gray-600 dark:text-gray-300">
                                    Book from any device, anywhere with our fully responsive platform.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                {/* How It Works Section */}
                <section className="py-20 px-4 sm:px-6 lg:px-8 bg-gray-50 dark:bg-gray-900">
                    <div className="max-w-7xl mx-auto">
                        <div className="text-center mb-16">
                            <h2 className="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                                How It Works
                            </h2>
                            <p className="text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                                Get your tickets in three simple steps.
                            </p>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-3 gap-8 md:gap-12">
                            {/* Step 1 */}
                            <div className="relative text-center">
                                <div className="hidden md:block absolute top-1/2 right-0 transform translate-x-1/2 -translate-y-1/2 z-10">
                                    <svg className="w-12 h-12 text-indigo-300 dark:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </div>
                                <div className="relative inline-block mb-6">
                                    <div className="w-20 h-20 bg-indigo-600 dark:bg-indigo-700 rounded-full flex items-center justify-center mx-auto shadow-lg">
                                        <svg className="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                    <div className="absolute -top-2 -right-2 w-8 h-8 bg-yellow-400 dark:bg-yellow-500 rounded-full flex items-center justify-center text-gray-900 dark:text-white font-bold text-sm shadow-md">
                                        1
                                    </div>
                                </div>
                                <h3 className="text-2xl font-semibold text-gray-900 dark:text-white mb-3">
                                    Browse Events
                                </h3>
                                <p className="text-gray-600 dark:text-gray-300">
                                    Explore our curated list of upcoming events and find something exciting.
                                </p>
                            </div>

                            {/* Step 2 */}
                            <div className="relative text-center">
                                <div className="hidden md:block absolute top-1/2 right-0 transform translate-x-1/2 -translate-y-1/2 z-10">
                                    <svg className="w-12 h-12 text-indigo-300 dark:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </div>
                                <div className="relative inline-block mb-6">
                                    <div className="w-20 h-20 bg-indigo-600 dark:bg-indigo-700 rounded-full flex items-center justify-center mx-auto shadow-lg">
                                        <svg className="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </div>
                                    <div className="absolute -top-2 -right-2 w-8 h-8 bg-yellow-400 dark:bg-yellow-500 rounded-full flex items-center justify-center text-gray-900 dark:text-white font-bold text-sm shadow-md">
                                        2
                                    </div>
                                </div>
                                <h3 className="text-2xl font-semibold text-gray-900 dark:text-white mb-3">
                                    Select Tickets
                                </h3>
                                <p className="text-gray-600 dark:text-gray-300">
                                    Choose how many tickets you need and see real-time availability.
                                </p>
                            </div>

                            {/* Step 3 */}
                            <div className="text-center">
                                <div className="relative inline-block mb-6">
                                    <div className="w-20 h-20 bg-indigo-600 dark:bg-indigo-700 rounded-full flex items-center justify-center mx-auto shadow-lg">
                                        <svg className="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <div className="absolute -top-2 -right-2 w-8 h-8 bg-yellow-400 dark:bg-yellow-500 rounded-full flex items-center justify-center text-gray-900 dark:text-white font-bold text-sm shadow-md">
                                        3
                                    </div>
                                </div>
                                <h3 className="text-2xl font-semibold text-gray-900 dark:text-white mb-3">
                                    Book & Go
                                </h3>
                                <p className="text-gray-600 dark:text-gray-300">
                                    Secure your spot and get ready for the event. It's that simple!
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                {/* Preview Events Section */}
                <section className="py-20 px-4 sm:px-6 lg:px-8 bg-white dark:bg-gray-800">
                    <div className="max-w-7xl mx-auto">
                        <div className="text-center mb-16">
                            <h2 className="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                                See What's Coming
                            </h2>
                            <p className="text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                                Check out some of our featured upcoming events.
                            </p>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                            {/* Event Card 1 */}
                            <div className="bg-gray-50 dark:bg-gray-700/50 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-600 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                                <div className="h-48 bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                                    <svg className="w-16 h-16 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                    </svg>
                                </div>
                                <div className="p-6">
                                    <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                        Tech Conference 2024
                                    </h3>
                                    <div className="space-y-2 mb-4">
                                        <div className="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                            <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            March 15, 2024
                                        </div>
                                        <div className="flex items-center text-sm">
                                            <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                            </svg>
                                            <span className="font-medium text-green-600 dark:text-green-400">150 tickets available</span>
                                        </div>
                                    </div>
                                    <Link
                                        href="/events"
                                        className="block w-full text-center bg-indigo-600 dark:bg-indigo-700 text-white px-4 py-3 rounded-lg font-medium hover:bg-indigo-700 dark:hover:bg-indigo-600 transition-all duration-200 min-h-[44px] flex items-center justify-center"
                                    >
                                        View Details
                                    </Link>
                                </div>
                            </div>

                            {/* Event Card 2 */}
                            <div className="bg-gray-50 dark:bg-gray-700/50 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-600 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                                <div className="h-48 bg-gradient-to-br from-green-500 to-teal-600 flex items-center justify-center">
                                    <svg className="w-16 h-16 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                    </svg>
                                </div>
                                <div className="p-6">
                                    <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                        Music Festival
                                    </h3>
                                    <div className="space-y-2 mb-4">
                                        <div className="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                            <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            April 5, 2024
                                        </div>
                                        <div className="flex items-center text-sm">
                                            <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                            </svg>
                                            <span className="font-medium text-green-600 dark:text-green-400">500 tickets available</span>
                                        </div>
                                    </div>
                                    <Link
                                        href="/events"
                                        className="block w-full text-center bg-indigo-600 dark:bg-indigo-700 text-white px-4 py-3 rounded-lg font-medium hover:bg-indigo-700 dark:hover:bg-indigo-600 transition-all duration-200 min-h-[44px] flex items-center justify-center"
                                    >
                                        View Details
                                    </Link>
                                </div>
                            </div>

                            {/* Event Card 3 */}
                            <div className="bg-gray-50 dark:bg-gray-700/50 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-600 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                                <div className="h-48 bg-gradient-to-br from-orange-500 to-red-600 flex items-center justify-center">
                                    <svg className="w-16 h-16 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div className="p-6">
                                    <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                        Networking Meetup
                                    </h3>
                                    <div className="space-y-2 mb-4">
                                        <div className="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                            <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            April 20, 2024
                                        </div>
                                        <div className="flex items-center text-sm">
                                            <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                            </svg>
                                            <span className="font-medium text-green-600 dark:text-green-400">75 tickets available</span>
                                        </div>
                                    </div>
                                    <Link
                                        href="/events"
                                        className="block w-full text-center bg-indigo-600 dark:bg-indigo-700 text-white px-4 py-3 rounded-lg font-medium hover:bg-indigo-700 dark:hover:bg-indigo-600 transition-all duration-200 min-h-[44px] flex items-center justify-center"
                                    >
                                        View Details
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <div className="text-center">
                            <Link
                                href="/events"
                                className="inline-flex items-center bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-400 px-8 py-4 rounded-xl font-semibold text-lg border-2 border-indigo-600 dark:border-indigo-400 hover:bg-indigo-600 dark:hover:bg-indigo-400 hover:text-white dark:hover:text-white transition-all duration-200 min-h-[44px]"
                            >
                                View All Events
                                <svg className="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </Link>
                        </div>
                    </div>
                </section>

                {/* Testimonials Section */}
                <section className="py-20 px-4 sm:px-6 lg:px-8 bg-gray-50 dark:bg-gray-900">
                    <div className="max-w-7xl mx-auto">
                        <div className="text-center mb-16">
                            <h2 className="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                                What Our Users Say
                            </h2>
                            <p className="text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                                Join thousands of satisfied users who trust TicketBook for their event bookings.
                            </p>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                            {/* Testimonial 1 */}
                            <div className="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700">
                                <div className="flex items-center mb-4">
                                    {[...Array(5)].map((_, i) => (
                                        <svg key={i} className="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    ))}
                                </div>
                                <p className="text-gray-600 dark:text-gray-300 mb-6 italic">
                                    "TicketBook made it so easy to get tickets for our company retreat. The interface is intuitive and the booking process was seamless!"
                                </p>
                                <div className="flex items-center">
                                    <div className="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center">
                                        <span className="text-indigo-600 dark:text-indigo-400 font-semibold text-lg">SM</span>
                                    </div>
                                    <div className="ml-4">
                                        <p className="font-semibold text-gray-900 dark:text-white">Sarah Miller</p>
                                        <p className="text-sm text-gray-500 dark:text-gray-400">Event Organizer</p>
                                    </div>
                                </div>
                            </div>

                            {/* Testimonial 2 */}
                            <div className="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700">
                                <div className="flex items-center mb-4">
                                    {[...Array(5)].map((_, i) => (
                                        <svg key={i} className="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    ))}
                                </div>
                                <p className="text-gray-600 dark:text-gray-300 mb-6 italic">
                                    "I love how I can see real-time ticket availability. It saved me from driving to a sold-out event. Highly recommend!"
                                </p>
                                <div className="flex items-center">
                                    <div className="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                                        <span className="text-green-600 dark:text-green-400 font-semibold text-lg">JD</span>
                                    </div>
                                    <div className="ml-4">
                                        <p className="font-semibold text-gray-900 dark:text-white">John Davis</p>
                                        <p className="text-sm text-gray-500 dark:text-gray-400">Event Attendee</p>
                                    </div>
                                </div>
                            </div>

                            {/* Testimonial 3 */}
                            <div className="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700">
                                <div className="flex items-center mb-4">
                                    {[...Array(5)].map((_, i) => (
                                        <svg key={i} className="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    ))}
                                </div>
                                <p className="text-gray-600 dark:text-gray-300 mb-6 italic">
                                    "The best ticket booking platform I've used. Clean design, fast performance, and excellent customer support. A+ experience!"
                                </p>
                                <div className="flex items-center">
                                    <div className="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                                        <span className="text-purple-600 dark:text-purple-400 font-semibold text-lg">EW</span>
                                    </div>
                                    <div className="ml-4">
                                        <p className="font-semibold text-gray-900 dark:text-white">Emily Watson</p>
                                        <p className="text-sm text-gray-500 dark:text-gray-400">Regular User</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* CTA Section */}
                <section className="py-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-indigo-600 via-purple-600 to-indigo-700 dark:from-indigo-900 dark:via-purple-900 dark:to-indigo-900 relative overflow-hidden">
                    {/* Background decoration */}
                    <div className="absolute inset-0 opacity-10">
                        <svg className="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                            <defs>
                                <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                                    <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" strokeWidth="0.5"/>
                                </pattern>
                            </defs>
                            <rect width="100" height="100" fill="url(#grid)" />
                        </svg>
                    </div>

                    <div className="max-w-4xl mx-auto text-center relative z-10">
                        <h2 className="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-6">
                            Ready to Book Your First Event?
                        </h2>
                        <p className="text-xl text-indigo-100 dark:text-indigo-200 mb-10 max-w-2xl mx-auto">
                            Join thousands of users who trust us for their ticket bookings. Get started in minutes.
                        </p>
                        <div className="flex flex-col sm:flex-row gap-4 justify-center">
                            <Link
                                href="/register"
                                className="bg-white text-indigo-600 dark:text-indigo-700 px-8 py-4 rounded-xl font-semibold text-lg hover:bg-gray-100 transition-all duration-200 hover:scale-105 shadow-xl min-h-[44px] flex items-center justify-center"
                            >
                                Create Free Account
                                <svg className="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </Link>
                            <Link
                                href="/login"
                                className="bg-transparent text-white px-8 py-4 rounded-xl font-semibold text-lg border-2 border-white hover:bg-white hover:text-indigo-600 transition-all duration-200 min-h-[44px] flex items-center justify-center"
                            >
                                Sign In
                            </Link>
                        </div>
                    </div>
                </section>

                {/* Footer */}
                <footer className="bg-gray-900 dark:bg-gray-950 py-12 px-4 sm:px-6 lg:px-8">
                    <div className="max-w-7xl mx-auto">
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                            {/* Logo and description */}
                            <div>
                                <Link href="/" className="flex items-center space-x-2 mb-4">
                                    <svg
                                        viewBox="0 0 64 64"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                        className="h-8 w-auto"
                                    >
                                        <defs>
                                            <linearGradient id="footerTicketGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                                <stop offset="0%" stopColor="#818cf8" />
                                                <stop offset="100%" stopColor="#6366f1" />
                                            </linearGradient>
                                        </defs>
                                        <rect x="4" y="16" width="56" height="32" rx="8" fill="url(#footerTicketGradient)" />
                                        <circle cx="4" cy="32" r="4" fill="#1f2937" />
                                        <circle cx="60" cy="32" r="4" fill="#1f2937" />
                                        <rect x="14" y="22" width="36" height="20" rx="4" fill="white" opacity="0.95" />
                                        <path d="M32 26l2 4h4l-3 2 1 4-4-2-4 2 1-4-3-2 4h4z" fill="#6366f1" />
                                    </svg>
                                    <span className="text-xl font-bold text-white">TicketBook</span>
                                </Link>
                                <p className="text-gray-400 text-sm">
                                    Your trusted platform for effortless event ticket booking. Discover, book, and enjoy amazing events.
                                </p>
                            </div>

                            {/* Navigation Links */}
                            <div>
                                <h3 className="text-white font-semibold mb-4">Quick Links</h3>
                                <ul className="space-y-2">
                                    <li>
                                        <Link
                                            href="/events"
                                            className="text-gray-400 hover:text-white transition-colors"
                                        >
                                            Events
                                        </Link>
                                    </li>
                                    <li>
                                        <a
                                            href="#features"
                                            className="text-gray-400 hover:text-white transition-colors"
                                        >
                                            Features
                                        </a>
                                    </li>
                                    <li>
                                        <a
                                            href="#how-it-works"
                                            className="text-gray-400 hover:text-white transition-colors"
                                        >
                                            How It Works
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            {/* Contact Links */}
                            <div>
                                <h3 className="text-white font-semibold mb-4">Support</h3>
                                <ul className="space-y-2">
                                    <li>
                                        <a
                                            href="mailto:support@ticketbook.com"
                                            className="text-gray-400 hover:text-white transition-colors"
                                        >
                                            Contact Us
                                        </a>
                                    </li>
                                    <li>
                                        <a
                                            href="#"
                                            className="text-gray-400 hover:text-white transition-colors"
                                        >
                                            FAQ
                                        </a>
                                    </li>
                                    <li>
                                        <a
                                            href="#"
                                            className="text-gray-400 hover:text-white transition-colors"
                                        >
                                            Privacy Policy
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div className="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center">
                            <p className="text-gray-500 text-sm">
                                &copy; 2024 TicketBook. All rights reserved.
                            </p>
                            <div className="flex space-x-6 mt-4 md:mt-0">
                                {/* Social icons - placeholder */}
                                <a href="#" className="text-gray-500 hover:text-white transition-colors" aria-label="Twitter">
                                    <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
                                    </svg>
                                </a>
                                <a href="https://github.com/gurodrigues-dev" className="text-gray-500 hover:text-white transition-colors" aria-label="GitHub">
                                    <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path fillRule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clipRule="evenodd" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
