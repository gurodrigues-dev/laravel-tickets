import React, { useState } from 'react';
import axios from 'axios';
import Modal from './Modal';
import { getErrorMessage } from '@/utils/errorHandler';
import { ButtonSpinner } from '@/Components/Spinner';

export default function BookingModal({ isOpen, onClose, event, onSuccess }) {
    const [quantity, setQuantity] = useState(1);
    const [error, setError] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [shakeQuantity, setShakeQuantity] = useState(false);

    const availableTickets = event?.available_tickets || 0;
    const maxTicketsPerUser = event?.max_tickets_per_user || 5;
    const maxAllowed = Math.min(availableTickets, maxTicketsPerUser);
    const remainingTickets = availableTickets - quantity;
    const isAtMaxLimit = quantity >= maxAllowed;
    const isOverLimit = quantity > maxTicketsPerUser || quantity > availableTickets;

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

    const triggerShakeAnimation = () => {
        setShakeQuantity(true);
        setTimeout(() => setShakeQuantity(false), 500);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();

        if (!validate()) {
            triggerShakeAnimation();
            return;
        }

        setIsSubmitting(true);
        setError('');

        try {
            const response = await axios.post('/api/v1/reservations', {
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
        setShakeQuantity(false);
        onClose();
    };

    return (
        <>
            <Modal show={isOpen} onClose={handleClose} maxWidth="md">
                <style dangerouslySetInnerHTML={{
                    __html: `
                        @keyframes shake {
                            0%, 100% { transform: translateX(0); }
                            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
                            20%, 40%, 60%, 80% { transform: translateX(5px); }
                        }
                        .animate-shake {
                            animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
                        }
                    `
                }} />
                <form onSubmit={handleSubmit}>
                    <div className="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 className="text-lg font-medium text-gray-900 dark:text-gray-100">Book Tickets</h3>
                </div>

                <div className="px-6 py-4 space-y-4">
                    <div>
                         <h4 className="text-xl font-semibold text-gray-900 dark:text-gray-100">{event?.name}</h4>
                         <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                             Available tickets: <span className="font-medium text-gray-900 dark:text-gray-100">{availableTickets}</span>
                         </p>
                     </div>

                      <div>
                          <label htmlFor="quantity" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
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
                                    className="w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-indigo-500 dark:focus:border-indigo-400 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                    aria-label="Decrease quantity"
                                    title={quantity <= 1 ? 'Minimum quantity is 1' : undefined}
                                >
                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 12H4" />
                                    </svg>
                                </button>
                                <div className={`relative flex-1 h-10 sm:h-12 min-w-[80px] flex items-center justify-center rounded-md border transition-all ${
                                    isOverLimit
                                        ? 'border-red-500 bg-red-50 dark:bg-red-900/20'
                                        : 'border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700'
                                } ${shakeQuantity ? 'animate-shake' : ''}`}>
                                    <span className={`text-lg sm:text-xl font-semibold ${
                                        isOverLimit
                                            ? 'text-red-600 dark:text-red-400'
                                            : 'text-gray-900 dark:text-gray-100'
                                    }`}>{quantity}</span>
                                    {isAtMaxLimit && (
                                        <span className="absolute -top-2 -right-2 px-1.5 py-0.5 text-[10px] font-bold text-white bg-red-500 rounded-full shadow-sm">
                                            MAX
                                        </span>
                                    )}
                                </div>
                                <button
                                    type="button"
                                    onClick={() => {
                                        const newQty = Math.min(maxAllowed, quantity + 1);
                                        if (newQty > quantity) {
                                            setQuantity(newQty);
                                            setError('');
                                        } else {
                                            triggerShakeAnimation();
                                            if (maxAllowed === maxTicketsPerUser && maxAllowed < availableTickets) {
                                                setError(`Maximum ${maxTicketsPerUser} tickets per user allowed`);
                                            } else if (maxAllowed === availableTickets && availableTickets < maxTicketsPerUser) {
                                                setError(`Only ${availableTickets} tickets available`);
                                            } else if (maxAllowed === availableTickets && maxAllowed === maxTicketsPerUser) {
                                                setError(`Maximum ${maxTicketsPerUser} tickets allowed`);
                                            }
                                        }
                                    }}
                                    disabled={quantity >= maxAllowed || availableTickets === 0 || isSubmitting}
                                    className={`w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center rounded-md border transition-all focus:outline-none focus:ring-2 disabled:opacity-50 disabled:cursor-not-allowed ${
                                        isAtMaxLimit
                                            ? 'border-red-500 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 cursor-not-allowed'
                                            : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-indigo-500 dark:focus:border-indigo-400'
                                    }`}
                                    aria-label="Increase quantity"
                                    title={quantity >= maxAllowed ? `Maximum ${maxAllowed} tickets allowed` : undefined}
                                >
                                   <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                       <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                                   </svg>
                               </button>
                           </div>

                           {/* Error message - prominent and always visible when there's an error */}
                           {error && (
                               <div className="mt-2 flex items-start gap-2 p-3 bg-red-50 dark:bg-red-900/20 rounded-md border border-red-200 dark:border-red-800">
                                   <svg className="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                       <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                   </svg>
                                   <p className="text-sm font-medium text-red-600 dark:text-red-400">
                                       {error}
                                   </p>
                               </div>
                           )}

                           {/* Info messages - only shown when no error */}
                           {!error && (
                               <>
                                   {quantity > 0 && remainingTickets >= 0 && (
                                       <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                           Remaining after booking: {remainingTickets}
                                       </p>
                                   )}
                                   {isAtMaxLimit && remainingTickets >= 0 && !isOverLimit && (
                                       <p className="mt-1 text-sm text-amber-600 dark:text-amber-400">
                                           {maxAllowed === maxTicketsPerUser && maxAllowed < availableTickets
                                               ? `You've reached the maximum of ${maxTicketsPerUser} tickets per user`
                                               : maxAllowed === availableTickets && availableTickets < maxTicketsPerUser
                                               ? `Only ${availableTickets} tickets available`
                                               : `Maximum ${maxAllowed} tickets allowed`}
                                       </p>
                                   )}
                                </>
                            )}
                        </div>
                 </div>

                 <div className="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-3">
                     <button
                         type="button"
                         onClick={handleClose}
                         disabled={isSubmitting}
                         className="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors min-h-[44px]"
                     >
                         Cancel
                     </button>
                     <button
                         type="submit"
                         disabled={isSubmitting || availableTickets === 0}
                         className="px-4 py-2 text-sm font-medium text-white bg-indigo-600 dark:bg-indigo-700 border border-transparent rounded-md hover:bg-indigo-700 dark:hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors min-h-[44px] flex items-center justify-center gap-2"
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
        </>
     );
 }
