import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import axios from 'axios';

export default function Manage({ reservationId }) {
    const [reservation, setReservation] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        axios.get(`/api/reservations/${reservationId}`)
            .then(res => setReservation(res.data))
            .catch(() => setError("Reservation not found"))
            .finally(() => setLoading(false));
    }, []);

    const cancel = () => {
        axios.delete(`/api/reservations/${reservationId}`)
            .then(() => alert("Cancelled"))
            .catch(() => alert("Error cancelling"));
    };

    if (loading) return <p>Loading...</p>;
    if (error) return <p>{error}</p>;

    return (
        <AuthenticatedLayout>
            <Head title="Manage Reservation" />

            <div className="p-6">
                <h1 className="text-2xl mb-4">Manage Reservation</h1>

                <p>Event: {reservation.event.name}</p>
                <p>Quantity: {reservation.quantity}</p>
                <p>Status: {reservation.status}</p>

                <button
                    onClick={cancel}
                    className="mt-4 bg-red-500 text-white px-4 py-2 rounded"
                >
                    Cancel Reservation
                </button>
            </div>
        </AuthenticatedLayout>
    );
}
