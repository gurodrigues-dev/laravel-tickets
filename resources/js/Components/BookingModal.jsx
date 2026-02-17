import React, { useState } from 'react';
import axios from 'axios';
import Modal from './Modal';

export default function BookingModal({ isOpen, onClose, event, onSuccess }) {
    const [quantity, setQuantity] = useState(1);
    const [error, setError] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);

    const availableTickets = event?.available_tickets || 0;
    const remainingTickets = availableTickets - quantity;

    const handleQuantityChange = (e) => {
        const value = parseInt(e.target.value) || 0;
        setQuantity(value);
        setError('');
    };

    const validate = () => {
        if (quantity < 1) {
            setError('Quantity must be at least 1');
            return false;
        }
        if (quantity > availableTickets) {
            setError('Not enough tickets available');
            return false;
        }
        return true;
    };

    const handleSubmit = async (e) => {
        e.preventDefault();

        if (!validate()) {
            return;
        }

        setIsSubmitting(true);
        setError('');

        try {
            const response = await axios.post('/api/reservations', {
                event_id: event.id,
                quantity: quantity,
                version: event.version || 0,
            });

            onSuccess(response.data);
            onClose();
            setQuantity(1);
        } catch (err) {
            if (err.response?.data?.message) {
                setError(err.response.data.message);
            } else {
                setError('Failed to book tickets. Please try again.');
            }
        } finally {
            setIsSubmitting(false);
        }
    };

    const handleClose = () => {
        setQuantity(1);
        setError('');
        onClose();
    };

    return (
        <Modal show={isOpen} onClose={handleClose} maxWidth="md">
            <form onSubmit={handleSubmit}>
                <div className="px-6 py-4 border-b border-gray-200">
                    <h3 className="text-lg font-medium text-gray-900">Book Tickets</h3>
                </div>

                <div className="px-6 py-4 space-y-4">
                    <div>
                        <h4 className="text-xl font-semibold text-gray-900">{event?.name}</h4>
                        <p className="mt-1 text-sm text-gray-600">
                            Available tickets: <span className="font-medium text-gray-900">{availableTickets}</span>
                        </p>
                    </div>

                    <div>
                        <label htmlFor="quantity" className="block text-sm font-medium text-gray-700">
                            Quantity
                        </label>
                        <input
                            type="number"
                            id="quantity"
                            name="quantity"
                            min="1"
                            max={availableTickets}
                            value={quantity}
                            onChange={handleQuantityChange}
                            disabled={availableTickets === 0}
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border"
                        />
                        {quantity > 0 && remainingTickets >= 0 && (
                            <p className="mt-1 text-sm text-gray-500">
                                Remaining after booking: {remainingTickets}
                            </p>
                        )}
                    </div>

                    {error && (
                        <div className="rounded-md bg-red-50 p-4">
                            <p className="text-sm text-red-800">{error}</p>
                        </div>
                    )}
                </div>

                <div className="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                    <button
                        type="button"
                        onClick={handleClose}
                        className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        disabled={isSubmitting || availableTickets === 0}
                        className="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {isSubmitting ? 'Booking...' : 'Book'}
                    </button>
                </div>
            </form>
        </Modal>
    );
}
