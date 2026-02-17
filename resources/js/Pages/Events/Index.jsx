import React, { useState, useEffect } from 'react';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import BookingModal from '@/Components/BookingModal';

export default function Index({ auth }) {
    const [events, setEvents] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [selectedEvent, setSelectedEvent] = useState(null);
    const [isModalOpen, setIsModalOpen] = useState(false);

    const fetchEvents = async () => {
        setLoading(true);
        try {
            const response = await axios.get('/api/events');
            setEvents(response.data);
            setError(null);
        } catch (error) {
            setError('Failed to load events. Please try again.');
            console.error('Failed to fetch events:', error);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchEvents();
    }, []);

    const handleBookTickets = (event) => {
        setSelectedEvent(event);
        setIsModalOpen(true);
    };

    const handleBookingSuccess = (reservation) => {
        // Refresh events list to show updated available_tickets
        fetchEvents();
    };

    const handleCloseModal = () => {
        setIsModalOpen(false);
        setSelectedEvent(null);
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Events</h2>}
        >
            <Head title="Events" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            {loading ? (
                                <div className="flex justify-center items-center h-64">
                                    <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
                                </div>
                            ) : error ? (
                                <div className="text-center py-12">
                                    <p className="text-red-600 mb-4">{error}</p>
                                    <button
                                        onClick={fetchEvents}
                                        className="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors"
                                    >
                                        Try Again
                                    </button>
                                </div>
                            ) : events.length === 0 ? (
                                <div className="text-center py-12">
                                    <p className="text-gray-500">No events available at the moment.</p>
                                </div>
                            ) : (
                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    {events.map((event) => (
                                        <div key={event.id} className="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                                            <div className="p-6">
                                                <h3 className="text-xl font-semibold text-gray-900 mb-2">
                                                    {event.name}
                                                </h3>
                                                {event.description && (
                                                    <p className="text-gray-600 mb-4 line-clamp-3">
                                                        {event.description}
                                                    </p>
                                                )}
                                                <div className="space-y-2 mb-4">
                                                    {event.event_date && (
                                                        <div className="flex items-center text-sm text-gray-500">
                                                            <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                            </svg>
                                                            {new Date(event.event_date).toLocaleDateString()}
                                                        </div>
                                                    )}
                                                    <div className="flex items-center text-sm">
                                                        <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                                        </svg>
                                                        <span className={`font-medium ${event.available_tickets > 0 ? 'text-green-600' : 'text-red-600'}`}>
                                                            {event.available_tickets} available
                                                        </span>
                                                        {event.total_tickets && (
                                                            <span className="text-gray-500 ml-1">/ {event.total_tickets}</span>
                                                        )}
                                                    </div>
                                                </div>
                                                <button
                                                    onClick={() => handleBookTickets(event)}
                                                    disabled={event.available_tickets === 0}
                                                    className="w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
                                                >
                                                    {event.available_tickets === 0 ? 'Sold Out' : 'Book Tickets'}
                                                </button>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            <BookingModal
                isOpen={isModalOpen}
                onClose={handleCloseModal}
                event={selectedEvent}
                onSuccess={handleBookingSuccess}
            />
        </AuthenticatedLayout>
    );
}
