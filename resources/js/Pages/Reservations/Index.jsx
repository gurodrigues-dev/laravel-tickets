import { useState, useEffect } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import Modal from '@/Components/Modal';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import DangerButton from '@/Components/DangerButton';
import axios from 'axios';
import { getErrorMessage } from '@/utils/errorHandler';
import Spinner, { ButtonSpinner } from '@/Components/Spinner';

export default function Index({ auth }) {
    const [reservations, setReservations] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [successMessage, setSuccessMessage] = useState('');

    const [updateModalOpen, setUpdateModalOpen] = useState(false);
    const [selectedReservation, setSelectedReservation] = useState(null);
    const [newQuantity, setNewQuantity] = useState('');
    const [updateLoading, setUpdateLoading] = useState(false);
    const [updateError, setUpdateError] = useState('');

    const [cancelModalOpen, setCancelModalOpen] = useState(false);
    const [cancelLoading, setCancelLoading] = useState(false);

    const fetchReservations = async () => {
        setLoading(true);
        setError(null);
        try {
            const response = await axios.get('/api/reservations/my-reservations');
            setReservations(response.data);
        } catch (err) {
            setError(getErrorMessage(err));
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchReservations();
    }, []);

    const openUpdateModal = (reservation) => {
        setSelectedReservation(reservation);
        setNewQuantity(reservation.quantity.toString());
        setUpdateError('');
        setUpdateModalOpen(true);
    };

    const handleUpdateQuantity = async (e) => {
        e.preventDefault();
        setUpdateError('');
        setUpdateLoading(true);

        const quantity = parseInt(newQuantity);
        const maxTicketsPerUser = selectedReservation.event.max_tickets_per_user || 5;

        if (isNaN(quantity) || quantity <= 0) {
            setUpdateError('Please enter a valid positive number.');
            setUpdateLoading(false);
            return;
        }

        if (quantity > selectedReservation.event.available_tickets) {
            setUpdateError(`Only ${selectedReservation.event.available_tickets} tickets available.`);
            setUpdateLoading(false);
            return;
        }

        if (quantity > maxTicketsPerUser) {
            setUpdateError(`Maximum ${maxTicketsPerUser} tickets per user.`);
            setUpdateLoading(false);
            return;
        }

        const otherReservationsForEvent = reservations.filter(
            r => r.event_id === selectedReservation.event.id && r.id !== selectedReservation.id && r.status !== 'cancelled'
        );
        const otherTicketsCount = otherReservationsForEvent.reduce((sum, r) => sum + r.quantity, 0);
        const totalTickets = quantity + otherTicketsCount;

        if (totalTickets > maxTicketsPerUser) {
            setUpdateError(`You already have ${otherTicketsCount} ticket(s) for this event. Maximum ${maxTicketsPerUser} tickets per user allowed.`);
            setUpdateLoading(false);
            return;
        }

        try {
            await axios.put(`/api/reservations/${selectedReservation.id}`, {
                quantity: quantity,
                version: selectedReservation.event.version,
            });

            setSuccessMessage(`Reservation updated to ${quantity} ticket(s).`);
            setUpdateModalOpen(false);
            fetchReservations();

            setTimeout(() => setSuccessMessage(''), 3000);
        } catch (err) {
            setUpdateError(getErrorMessage(err));
        } finally {
            setUpdateLoading(false);
        }
    };

    const openCancelModal = (reservation) => {
        setSelectedReservation(reservation);
        setCancelModalOpen(true);
    };

    const handleCancelReservation = async () => {
        setCancelLoading(true);

        try {
            await axios.delete(`/api/reservations/${selectedReservation.id}`);
            setSuccessMessage('Reservation cancelled successfully.');
            setCancelModalOpen(false);
            fetchReservations();

            setTimeout(() => setSuccessMessage(''), 3000);
        } catch (err) {
            setError(getErrorMessage(err));
        } finally {
            setCancelLoading(false);
        }
    };

    const formatDate = (dateString) => {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    const getStatusClasses = (status) => {
        switch (status.toLowerCase()) {
            case 'confirmed':
                return 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300';
            case 'pending':
                return 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300';
            case 'cancelled':
                return 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300';
            default:
                return 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300';
        }
    };

    const updateModalValues = selectedReservation ? (() => {
        const maxTicketsPerUser = selectedReservation.event.max_tickets_per_user || 5;
        const otherReservationsForEvent = reservations.filter(
            r => r.event_id === selectedReservation.event.id && r.id !== selectedReservation.id && r.status !== 'cancelled'
        );
        const otherTicketsCount = otherReservationsForEvent.reduce((sum, r) => sum + r.quantity, 0);
        const maxAllowedForCurrent = maxTicketsPerUser - otherTicketsCount;
        const maxInputValue = Math.min(selectedReservation.event.available_tickets, maxAllowedForCurrent);

        return { maxTicketsPerUser, otherTicketsCount, maxAllowedForCurrent, maxInputValue };
    })() : null;

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">My Reservations</h2>}
        >
            <Head title="My Reservations" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                             {/* Success Message */}
                            {successMessage && (
                                <div className="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 rounded-md">
                                    {successMessage}
                                </div>
                            )}

                            {/* Error Message */}
                            {error && (
                                <div className="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 rounded-md flex items-start">
                                    <svg className="h-5 w-5 text-red-400 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>{error}</span>
                                </div>
                            )}

                            {/* Loading State */}
                            {loading ? (
                                <div className="text-center py-12">
                                    <Spinner size="lg" text="Loading reservations..." />
                                </div>
                            ) : reservations.length === 0 ? (
                                /* Empty State */
                                <div className="text-center py-12">
                                    <svg
                                        className="mx-auto h-12 w-12 text-gray-400"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={2}
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                        />
                                    </svg>
                                    <h3 className="mt-2 text-sm font-medium text-gray-900">No reservations found</h3>
                                    <p className="mt-1 text-sm text-gray-500">You haven't made any reservations yet.</p>
                                </div>
                            ) : (
                                 /* Reservations List */
                                <div className="space-y-4">
                                    {reservations.map((reservation) => (
                                        <div
                                            key={reservation.id}
                                            className="border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:shadow-md transition-shadow bg-white dark:bg-gray-700/50"
                                        >
                                            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                                <div className="flex-1">
                                                    <h3 className="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                        {reservation.event.name}
                                                    </h3>
                                                    <p className="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                                        <svg
                                                            className="inline-block h-4 w-4 mr-1"
                                                            fill="none"
                                                            viewBox="0 0 24 24"
                                                            stroke="currentColor"
                                                        >
                                                            <path
                                                                strokeLinecap="round"
                                                                strokeLinejoin="round"
                                                                strokeWidth={2}
                                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                            />
                                                        </svg>
                                                        {formatDate(reservation.event.event_date)}
                                                    </p>
                                                     <div className="mt-2 flex flex-wrap items-center gap-3">
                                                        <span className="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                                                            <svg
                                                                className="inline-block h-4 w-4 mr-1"
                                                                fill="none"
                                                                viewBox="0 0 24 24"
                                                                stroke="currentColor"
                                                            >
                                                                <path
                                                                    strokeLinecap="round"
                                                                    strokeLinejoin="round"
                                                                    strokeWidth={2}
                                                                    d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"
                                                                />
                                                            </svg>
                                                            {reservation.quantity} ticket{reservation.quantity !== 1 ? 's' : ''}
                                                        </span>
                                                        <span
                                                            className={`inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${getStatusClasses(
                                                                reservation.status
                                                            )}`}
                                                        >
                                                            {reservation.status.charAt(0).toUpperCase() + reservation.status.slice(1)}
                                                        </span>
                                                    </div>
                                                </div>

                                                {reservation.status !== 'cancelled' && (
                                                    <div className="flex gap-2">
                                                        <PrimaryButton
                                                            onClick={() => openUpdateModal(reservation)}
                                                            className="text-xs min-h-[44px]"
                                                        >
                                                            Update Quantity
                                                        </PrimaryButton>
                                                         <DangerButton
                                                             onClick={() => openCancelModal(reservation)}
                                                             className="text-xs min-h-[44px]"
                                                         >
                                                             Cancel
                                                         </DangerButton>
                                                     </div>
                                                )}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            {/* Update Quantity Modal */}
            <Modal show={updateModalOpen} onClose={() => setUpdateModalOpen(false)} maxWidth="md">
                <div className="p-6">
                    <h3 className="text-lg font-medium text-gray-900 dark:text-gray-100">Update Reservation Quantity</h3>
                    <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Update the number of tickets for: {selectedReservation?.event.name}
                    </p>
                    <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Available tickets: {selectedReservation?.event.available_tickets}
                    </p>

                    {updateModalValues && (
                        <form onSubmit={handleUpdateQuantity} className="mt-6">
                            <div>
                                <label htmlFor="quantity" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Number of Tickets
                                </label>
                                <input
                                    id="quantity"
                                    type="number"
                                    min="1"
                                    max={updateModalValues.maxInputValue}
                                    value={newQuantity}
                                    onChange={(e) => setNewQuantity(e.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 sm:text-sm px-3 py-2 border"
                                />
                                <div className="mt-2 space-y-1">
                                    <p className="text-xs text-gray-500 dark:text-gray-400">
                                        Maximum {updateModalValues.maxTicketsPerUser} tickets per user
                                    </p>
                                    {updateModalValues.otherTicketsCount > 0 && (
                                        <p className="text-xs text-gray-500 dark:text-gray-400">
                                            You have {updateModalValues.otherTicketsCount} other ticket(s) for this event
                                        </p>
                                    )}
                                    <p className="text-xs text-gray-500 dark:text-gray-400">
                                        You can add up to {updateModalValues.maxAllowedForCurrent} more ticket(s)
                                    </p>
                                </div>
                                {updateError && (
                                    <p className="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-2">
                                        <svg className="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {updateError}
                                    </p>
                                )}
                            </div>

                            <div className="mt-6 flex justify-end gap-2">
                                <SecondaryButton
                                    type="button"
                                    onClick={() => setUpdateModalOpen(false)}
                                    disabled={updateLoading}
                                    className="min-h-[44px]"
                                >
                                    Cancel
                                </SecondaryButton>
                                <PrimaryButton type="submit" disabled={updateLoading} className="min-h-[44px]">
                                    {updateLoading ? (
                                        <span className="flex items-center gap-2">
                                            <ButtonSpinner />
                                            Updating...
                                        </span>
                                    ) : (
                                        'Update'
                                    )}
                                </PrimaryButton>
                            </div>
                        </form>
                    )}
                </div>
            </Modal>

            {/* Cancel Confirmation Modal */}
            <Modal show={cancelModalOpen} onClose={() => setCancelModalOpen(false)} maxWidth="sm">
                <div className="p-6">
                    <h3 className="text-lg font-medium text-gray-900 dark:text-gray-100">Cancel Reservation</h3>
                    <p className="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Are you sure you want to cancel your reservation for{' '}
                        <span className="font-medium text-gray-900 dark:text-gray-100">{selectedReservation?.event.name}</span>?
                    </p>
                    <p className="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        This action cannot be undone.
                    </p>

                    <div className="mt-6 flex justify-end gap-2">
                        <SecondaryButton
                            type="button"
                            onClick={() => setCancelModalOpen(false)}
                            disabled={cancelLoading}
                            className="min-h-[44px]"
                        >
                            Keep Reservation
                        </SecondaryButton>
                        <DangerButton onClick={handleCancelReservation} disabled={cancelLoading} className="min-h-[44px]">
                            {cancelLoading ? (
                                <span className="flex items-center gap-2">
                                    <ButtonSpinner />
                                    Cancelling...
                                </span>
                            ) : (
                                'Cancel Reservation'
                            )}
                        </DangerButton>
                    </div>
                </div>
            </Modal>
        </AuthenticatedLayout>
    );
}
