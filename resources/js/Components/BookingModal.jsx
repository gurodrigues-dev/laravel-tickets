import React, { useState } from 'react';
import axios from 'axios';
import Modal from './Modal';
import { getErrorMessage } from '@/utils/errorHandler';
import { ButtonSpinner } from '@/Components/Spinner';

export default function BookingModal({ isOpen, onClose, event, onSuccess }) {
    const [quantity, setQuantity] = useState(1);
    const [error, setError] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);

    const availableTickets = event?.available_tickets || 0;
    const maxTicketsPerUser = event?.max_tickets_per_user || 5;
    const maxAllowed = Math.min(availableTickets, maxTicketsPerUser);
    const remainingTickets = availableTickets - quantity;

    const validate = () => {
        if (quantity < 1) {
            setError('Quantity must be at least 1');
            return false;
        }
        if (quantity > availableTickets) {
            setError('Not enough tickets available');
            return false;
        }
        if (quantity > maxTicketsPerUser) {
            setError(`Maximum ${maxTicketsPerUser} tickets per user`);
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
            setError(getErrorMessage(err));
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
                          <div className="mt-1 flex items-center space-x-3">
                              <button
                                  type="button"
                                  onClick={() => {
                                      const newQty = Math.max(1, quantity - 1);
                                      setQuantity(newQty);
                                      setError('');
                                  }}
                                  disabled={quantity <= 1 || availableTickets === 0 || isSubmitting}
                                  className="w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                  aria-label="Decrease quantity"
                              >
                                  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 12H4" />
                                  </svg>
                              </button>
                              <div className="flex-1 h-10 sm:h-12 min-w-[60px] flex items-center justify-center rounded-md border border-gray-300 bg-gray-50">
                                  <span className="text-lg sm:text-xl font-semibold text-gray-900">{quantity}</span>
                              </div>
                              <button
                                  type="button"
                                  onClick={() => {
                                      const newQty = Math.min(maxAllowed, quantity + 1);
                                      if (newQty > quantity) {
                                          setQuantity(newQty);
                                          setError('');
                                      }
                                  }}
                                  disabled={quantity >= maxAllowed || availableTickets === 0 || isSubmitting}
                                  className="w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                  aria-label="Increase quantity"
                              >
                                  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                                  </svg>
                              </button>
                          </div>
                         {quantity > 0 && remainingTickets >= 0 && (
                             <p className="mt-1 text-sm text-gray-500">
                                 Remaining after booking: {remainingTickets}
                             </p>
                         )}
                         {quantity > maxTicketsPerUser && (
                             <p className="mt-1 text-sm text-red-600 font-medium">
                                 Maximum {maxTicketsPerUser} tickets per user
                             </p>
                         )}
                         {quantity > availableTickets && quantity <= maxTicketsPerUser && (
                             <p className="mt-1 text-sm text-red-600 font-medium">
                                 Not enough tickets available
                             </p>
                         )}
                     </div>

                      {error && (
                          <p className="mt-2 text-sm text-red-600 flex items-center gap-2">
                              <svg className="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                              </svg>
                              {error}
                          </p>
                      )}
                </div>

                 <div className="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                     <button
                         type="button"
                         onClick={handleClose}
                         disabled={isSubmitting}
                         className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors min-h-[44px]"
                     >
                         Cancel
                     </button>
                     <button
                         type="submit"
                         disabled={isSubmitting || availableTickets === 0}
                         className="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors min-h-[44px] flex items-center justify-center gap-2"
                     >
                         {isSubmitting ? (
                             <>
                                 <ButtonSpinner />
                                 Booking...
                             </>
                         ) : (
                             'Book'
                         )}
                     </button>
                 </div>
            </form>
        </Modal>
    );
}
