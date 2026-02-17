import { useState, useEffect } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import Modal from '@/Components/Modal';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import DangerButton from '@/Components/DangerButton';
import axios from 'axios';

export default function Index({ auth }) {
    const [reservations, setReservations] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [successMessage, setSuccessMessage] = useState('');

    // Update quantity modal state
    const [updateModalOpen, setUpdateModalOpen] = useState(false);
    const [selectedReservation, setSelectedReservation] = useState(null);
    const [newQuantity, setNewQuantity] = useState('');
    const [updateLoading, setUpdateLoading] = useState(false);
    const [updateError, setUpdateError] = useState('');

    // Cancel confirmation modal state
    const [cancelModalOpen, setCancelModalOpen] = useState(false);
    const [cancelLoading, setCancelLoading] = useState(false);

    // Fetch reservations
    const fetchReservations = async () => {
        setLoading(true);
        setError(null);
        try {
            const response = await axios.get('/api/my-reservations');
            setReservations(response.data);
        } catch (err) {
            setError('Failed to load reservations. Please try again.');
            console.error('Error fetching reservations:', err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchReservations();
    }, []);

    // Open update quantity modal
    const openUpdateModal = (reservation) => {
        setSelectedReservation(reservation);
        setNewQuantity(reservation.quantity.toString());
        setUpdateError('');
        setUpdateModalOpen(true);
    };

    // Handle quantity update
    const handleUpdateQuantity = async (e) => {
        e.preventDefault();
        setUpdateError('');
        setUpdateLoading(true);

        const quantity = parseInt(newQuantity);

        // Validation
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

        try {
            await axios.put(`/api/reservations/${selectedReservation.id}`, {
                quantity: quantity,
                version: selectedReservation.event.version,
            });

            setSuccessMessage(`Reservation updated to ${quantity} ticket(s).`);
            setUpdateModalOpen(false);
            fetchReservations();

            // Clear success message after 3 seconds
            setTimeout(() => setSuccessMessage(''), 3000);
        } catch (err) {
            if (err.response?.data?.error) {
                setUpdateError(err.response.data.error);
            } else if (err.response?.data?.message) {
                setUpdateError(err.response.data.message);
            } else {
                setUpdateError('Failed to update reservation. Please try again.');
            }
        } finally {
            setUpdateLoading(false);
        }
    };

    // Open cancel confirmation modal
    const openCancelModal = (reservation) => {
        setSelectedReservation(reservation);
        setCancelModalOpen(true);
    };

    // Handle cancel reservation
    const handleCancelReservation = async () => {
        setCancelLoading(true);

        try {
            await axios.delete(`/api/reservations/${selectedReservation.id}`);
            setSuccessMessage('Reservation cancelled successfully.');
            setCancelModalOpen(false);
            fetchReservations();

            // Clear success message after 3 seconds
            setTimeout(() => setSuccessMessage(''), 3000);
        } catch (err) {
            setError('Failed to cancel reservation. Please try again.');
            console.error('Error cancelling reservation:', err);
        } finally {
            setCancelLoading(false);
        }
    };

    // Format date for display
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

    // Get status badge classes
    const getStatusClasses = (status) => {
        switch (status.toLowerCase()) {
            case 'confirmed':
                return 'bg-green-100 text-green-800';
            case 'pending':
                return 'bg-yellow-100 text-yellow-800';
            case 'cancelled':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">My Reservations</h2>}
        >
            <Head title="My Reservations" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            {/* Success Message */}
                            {successMessage && (
                                <div className="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-md">
                                    {successMessage}
                                </div>
                            )}

                            {/* Error Message */}
                            {error && (
                                <div className="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-md">
                                    {error}
                                </div>
                            )}

                            {/* Loading State */}
                            {loading ? (
                                <div className="text-center py-12">
                                    <div className="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-gray-300 border-t-gray-800"></div>
                                    <p className="mt-4 text-gray-600">Loading reservations...</p>
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
                                            className="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow"
                                        >
                                            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                                <div className="flex-1">
                                                    <h3 className="text-lg font-semibold text-gray-900">
                                                        {reservation.event.name}
                                                    </h3>
                                                    <p className="mt-1 text-sm text-gray-600">
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
                                                        <span className="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
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
                                                            className="text-xs"
                                                        >
                                                            Update Quantity
                                                        </PrimaryButton>
                                                        <DangerButton
                                                            onClick={() => openCancelModal(reservation)}
                                                            className="text-xs"
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
                    <h3 className="text-lg font-medium text-gray-900">Update Reservation Quantity</h3>
                    <p className="mt-1 text-sm text-gray-600">
                        Update the number of tickets for: {selectedReservation?.event.name}
                    </p>
                    <p className="mt-1 text-xs text-gray-500">
                        Available tickets: {selectedReservation?.event.available_tickets}
                    </p>

                    <form onSubmit={handleUpdateQuantity} className="mt-6">
                        <div>
                            <label htmlFor="quantity" className="block text-sm font-medium text-gray-700">
                                Number of Tickets
                            </label>
                            <input
                                id="quantity"
                                type="number"
                                min="1"
                                max={selectedReservation?.event.available_tickets || 999}
                                value={newQuantity}
                                onChange={(e) => setNewQuantity(e.target.value)}
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border"
                            />
                            {updateError && (
                                <p className="mt-2 text-sm text-red-600">{updateError}</p>
                            )}
                        </div>

                        <div className="mt-6 flex justify-end gap-2">
                            <SecondaryButton
                                type="button"
                                onClick={() => setUpdateModalOpen(false)}
                                disabled={updateLoading}
                            >
                                Cancel
                            </SecondaryButton>
                            <PrimaryButton type="submit" disabled={updateLoading}>
                                {updateLoading ? 'Updating...' : 'Update'}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </Modal>

            {/* Cancel Confirmation Modal */}
            <Modal show={cancelModalOpen} onClose={() => setCancelModalOpen(false)} maxWidth="sm">
                <div className="p-6">
                    <h3 className="text-lg font-medium text-gray-900">Cancel Reservation</h3>
                    <p className="mt-2 text-sm text-gray-600">
                        Are you sure you want to cancel your reservation for{' '}
                        <span className="font-medium text-gray-900">{selectedReservation?.event.name}</span>?
                    </p>
                    <p className="mt-2 text-sm text-gray-600">
                        This action cannot be undone.
                    </p>

                    <div className="mt-6 flex justify-end gap-2">
                        <SecondaryButton
                            type="button"
                            onClick={() => setCancelModalOpen(false)}
                            disabled={cancelLoading}
                        >
                            Keep Reservation
                        </SecondaryButton>
                        <DangerButton onClick={handleCancelReservation} disabled={cancelLoading}>
                            {cancelLoading ? 'Cancelling...' : 'Cancel Reservation'}
                        </DangerButton>
                    </div>
                </div>
            </Modal>
        </AuthenticatedLayout>
    );
}
