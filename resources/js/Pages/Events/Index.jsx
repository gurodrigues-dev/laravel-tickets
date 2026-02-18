import React, { useState, useEffect, useMemo } from 'react';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import BookingModal from '@/Components/BookingModal';
import { getErrorMessage } from '@/utils/errorHandler';
import Spinner from '@/Components/Spinner';

export default function Index({ auth }) {
    const [events, setEvents] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [selectedEvent, setSelectedEvent] = useState(null);
    const [isModalOpen, setIsModalOpen] = useState(false);

    const [showOnlyUpcoming, setShowOnlyUpcoming] = useState(true);
    const [includePastEvents, setIncludePastEvents] = useState(false);
    const [startDate, setStartDate] = useState('');
    const [endDate, setEndDate] = useState('');
    const [sortBy, setSortBy] = useState('date-asc');
    const [searchQuery, setSearchQuery] = useState('');
    const [isFilterPanelOpen, setIsFilterPanelOpen] = useState(false);

    const fetchEvents = async () => {
        setLoading(true);
        try {
            const response = await axios.get('/api/events');
            setEvents(response.data);
            setError(null);
        } catch (error) {
            setError(getErrorMessage(error));
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchEvents();
    }, []);

    const handleBookTickets = (event) => {
        if (isEventPast(event.event_date)) {
            return;
        }
        setSelectedEvent(event);
        setIsModalOpen(true);
    };

    const handleBookingSuccess = (reservation) => {
        fetchEvents();
    };

    const handleCloseModal = () => {
        setIsModalOpen(false);
        setSelectedEvent(null);
    };

    const isEventPast = (eventDate) => {
        if (!eventDate) return false;
        return new Date(eventDate) < new Date();
    };

    const toggleFilterPanel = () => {
        setIsFilterPanelOpen(!isFilterPanelOpen);
    };

    const clearFilters = () => {
        setShowOnlyUpcoming(true);
        setIncludePastEvents(false);
        setStartDate('');
        setEndDate('');
        setSortBy('date-asc');
        setSearchQuery('');
        setIsFilterPanelOpen(false);
    };

    const hasActiveFilters = useMemo(() => {
        return !showOnlyUpcoming || includePastEvents || startDate || endDate || sortBy !== 'date-asc' || searchQuery;
    }, [showOnlyUpcoming, includePastEvents, startDate, endDate, sortBy, searchQuery]);

    const filteredEvents = useMemo(() => {
        let filtered = [...events];

        if (searchQuery) {
            const query = searchQuery.toLowerCase();
            filtered = filtered.filter(event =>
                event.name.toLowerCase().includes(query)
            );
        }

        if (startDate) {
            const start = new Date(startDate);
            filtered = filtered.filter(event => {
                if (!event.event_date) return false;
                return new Date(event.event_date) >= start;
            });
        }

        if (endDate) {
            const end = new Date(endDate);
            end.setHours(23, 59, 59, 999); // End of day
            filtered = filtered.filter(event => {
                if (!event.event_date) return false;
                return new Date(event.event_date) <= end;
            });
        }

        if (!includePastEvents) {
            filtered = filtered.filter(event => !isEventPast(event.event_date));
        }

        if (showOnlyUpcoming) {
            filtered = filtered.filter(event => !isEventPast(event.event_date));
        }

        switch (sortBy) {
            case 'tickets-desc':
                filtered.sort((a, b) => b.available_tickets - a.available_tickets);
                break;
            case 'tickets-asc':
                filtered.sort((a, b) => a.available_tickets - b.available_tickets);
                break;
            case 'date-desc':
                filtered.sort((a, b) => new Date(b.event_date) - new Date(a.event_date));
                break;
            case 'date-asc':
            default:
                filtered.sort((a, b) => new Date(a.event_date) - new Date(b.event_date));
                break;
        }

        return filtered;
    }, [events, showOnlyUpcoming, includePastEvents, startDate, endDate, sortBy, searchQuery]);

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
                            {/* Filter Button and Collapsible Panel */}
                            {!loading && (
                                <>
                                    {/* Filter Button */}
                                    <button
                                        onClick={toggleFilterPanel}
                                        className={`mb-4 w-full sm:w-auto px-4 py-2.5 rounded-lg font-medium text-sm transition-all duration-200 flex items-center justify-center gap-2 min-h-[44px] ${
                                            hasActiveFilters
                                                ? 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-md'
                                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                        }`}
                                    >
                                        <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                        </svg>
                                        <span>Filters</span>
                                        {hasActiveFilters && (
                                            <span className="ml-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-white/20">
                                                Active
                                            </span>
                                        )}
                                        <svg
                                            className={`w-4 h-4 ml-1 transition-transform duration-200 ${isFilterPanelOpen ? 'rotate-180' : ''}`}
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    {/* Collapsible Filter Panel */}
                                    <div
                                        className={`overflow-hidden transition-all duration-300 ease-in-out ${
                                            isFilterPanelOpen ? 'max-h-[600px] opacity-100' : 'max-h-0 opacity-0'
                                        }`}
                                    >
                                        <div className="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                                                <h3 className="text-lg font-medium text-gray-900 flex items-center gap-2">
                                                    <svg className="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                                    </svg>
                                                    Filters
                                                </h3>
                                                {hasActiveFilters && (
                                                    <button
                                                        onClick={clearFilters}
                                                        className="text-sm text-indigo-600 hover:text-indigo-800 flex items-center gap-1 px-3 py-2 rounded-md hover:bg-indigo-50 transition-colors min-h-[44px]"
                                                    >
                                                        <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                        Clear Filters
                                                    </button>
                                                )}
                                            </div>

                                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                                {/* Search by Name */}
                                                <div className="lg:col-span-3">
                                                    <label htmlFor="search" className="block text-sm font-medium text-gray-700 mb-1">
                                                        Search Events
                                                    </label>
                                                    <div className="relative">
                                                        <input
                                                            id="search"
                                                            type="text"
                                                            placeholder="Search by event name..."
                                                            value={searchQuery}
                                                            onChange={(e) => setSearchQuery(e.target.value)}
                                                            className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                        />
                                                        <svg className="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                        </svg>
                                                    </div>
                                                </div>

                                                {/* Sort By */}
                                                <div>
                                                    <label htmlFor="sort" className="block text-sm font-medium text-gray-700 mb-1">
                                                        Sort By
                                                    </label>
                                                    <select
                                                        id="sort"
                                                        value={sortBy}
                                                        onChange={(e) => setSortBy(e.target.value)}
                                                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                    >
                                                        <option value="date-asc">Date: Oldest First</option>
                                                        <option value="date-desc">Date: Newest First</option>
                                                        <option value="tickets-desc">Tickets: Most Available</option>
                                                        <option value="tickets-asc">Tickets: Least Available</option>
                                                    </select>
                                                </div>

                                                {/* Date Range - Start */}
                                                <div>
                                                    <label htmlFor="startDate" className="block text-sm font-medium text-gray-700 mb-1">
                                                        Start Date
                                                    </label>
                                                    <input
                                                        id="startDate"
                                                        type="date"
                                                        value={startDate}
                                                        onChange={(e) => setStartDate(e.target.value)}
                                                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                    />
                                                </div>

                                                {/* Date Range - End */}
                                                <div>
                                                    <label htmlFor="endDate" className="block text-sm font-medium text-gray-700 mb-1">
                                                        End Date
                                                    </label>
                                                    <input
                                                        id="endDate"
                                                        type="date"
                                                        value={endDate}
                                                        onChange={(e) => setEndDate(e.target.value)}
                                                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                    />
                                                </div>

                                                {/* Show Only Upcoming */}
                                                <div className="flex items-center">
                                                    <label className="flex items-center cursor-pointer">
                                                        <input
                                                            type="checkbox"
                                                            checked={showOnlyUpcoming}
                                                            onChange={(e) => setShowOnlyUpcoming(e.target.checked)}
                                                            className="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                                        />
                                                        <span className="ml-2 text-sm text-gray-700 flex items-center gap-1">
                                                            <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                            Show Only Upcoming
                                                        </span>
                                                    </label>
                                                </div>

                                                {/* Include Past Events */}
                                                <div className="flex items-center">
                                                    <label className="flex items-center cursor-pointer">
                                                        <input
                                                            type="checkbox"
                                                            checked={includePastEvents}
                                                            onChange={(e) => setIncludePastEvents(e.target.checked)}
                                                            className="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                                        />
                                                        <span className="ml-2 text-sm text-gray-700 flex items-center gap-1">
                                                            <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                            </svg>
                                                            Include Past Events
                                                        </span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </>
                            )}

                            {loading ? (
                                <div className="flex justify-center items-center h-64">
                                    <Spinner size="lg" text="Loading events..." />
                                </div>
                            ) : error ? (
                                <div className="text-center py-12">
                                    <svg className="mx-auto h-12 w-12 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <p className="mt-4 text-red-600">{error}</p>
                                    <button
                                        onClick={fetchEvents}
                                        className="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors min-h-[44px]"
                                    >
                                        Try Again
                                    </button>
                                </div>
                            ) : filteredEvents.length === 0 ? (
                                <div className="text-center py-12">
                                    <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <h3 className="mt-2 text-sm font-medium text-gray-900">No events found</h3>
                                    <p className="mt-1 text-sm text-gray-500">
                                        {hasActiveFilters ? 'Try adjusting your filters or search.' : 'No events available at the moment.'}
                                    </p>
                                    {hasActiveFilters && (
                                        <button
                                            onClick={clearFilters}
                                            className="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors min-h-[44px]"
                                        >
                                            Clear Filters
                                        </button>
                                    )}
                                </div>
                            ) : (
                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                     {filteredEvents.map((event) => {
                                         const isPast = isEventPast(event.event_date);
                                         return (
                                             <div key={event.id} className={`border rounded-lg overflow-hidden transition-shadow ${isPast ? 'border-gray-200 bg-gray-50' : 'border-gray-200 hover:shadow-lg'}`}>
                                                 <div className="p-6">
                                                     <h3 className={`text-xl font-semibold mb-2 ${isPast ? 'text-gray-500' : 'text-gray-900'}`}>
                                                         {event.name}
                                                     </h3>
                                                     {event.description && (
                                                         <p className={`mb-4 line-clamp-3 ${isPast ? 'text-gray-400' : 'text-gray-600'}`}>
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
                                                                 {isPast && (
                                                                     <span className="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                                         Event ended
                                                                     </span>
                                                                 )}
                                                             </div>
                                                         )}
                                                          <div className="flex items-center text-sm">
                                                              <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                                              </svg>
                                                              <span className={`font-medium ${event.available_tickets > 0 ? 'text-green-600' : 'text-red-600'}`}>
                                                                  {event.available_tickets} available
                                                              </span>
                                                              {event.total_tickets != null ? (
                                                                  <span className="text-gray-500"> / {event.total_tickets}</span>
                                                              ) : null}
                                                          </div>
                                                     </div>
                                                      <button
                                                          onClick={() => handleBookTickets(event)}
                                                          disabled={event.available_tickets === 0 || isPast}
                                                          className="w-full px-4 py-3 text-sm font-medium rounded-md transition-colors disabled:cursor-not-allowed min-h-[44px] ${
                                                              isPast
                                                                  ? 'bg-gray-300 text-gray-500'
                                                                  : event.available_tickets === 0
                                                                      ? 'bg-gray-300 text-gray-500'
                                                                      : 'bg-indigo-600 text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500'
                                                          }"
                                                      >
                                                          {isPast ? 'Event Ended' : event.available_tickets === 0 ? 'Sold Out' : 'Book Tickets'}
                                                      </button>
                                                 </div>
                                             </div>
                                         );
                                     })}
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
