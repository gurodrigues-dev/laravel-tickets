import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import axios from 'axios';

export default function Create({ eventId }) {
    const [quantity, setQuantity] = useState(1);
    const [maxTickets, setMaxTickets] = useState(1);
    const [error, setError] = useState(null);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        axios.get('/api/config')
            .then(res => setMaxTickets(res.data.maxTicketsPerReservation));
    }, []);

    const submit = () => {
        setLoading(true);
        setError(null);

        axios.post('/api/reservations', {
            event_id: eventId,
            quantity: quantity
        })
        .then(() => {
            alert("Reservation created!");
        })
        .catch(err => {
            if (err.response?.status === 422) {
                setError("Validation error");
            } else if (err.response?.status === 409) {
                setError("Not enough tickets available");
            } else {
                setError("Unexpected error");
            }
        })
        .finally(() => setLoading(false));
    };

    return (
        <AuthenticatedLayout>
            <Head title="Create Reservation" />

            <div className="p-6 max-w-md">
                <h1 className="text-2xl mb-4">Create Reservation</h1>

                {error && <div className="bg-red-100 text-red-600 p-2 mb-3">{error}</div>}

                <input
                    type="number"
                    min="1"
                    max={maxTickets}
                    value={quantity}
                    onChange={e => setQuantity(e.target.value)}
                    className="border p-2 w-full mb-3"
                />

                <button
                    onClick={submit}
                    disabled={loading}
                    className="bg-green-500 text-white px-4 py-2 rounded w-full"
                >
                    {loading ? "Processing..." : "Confirm"}
                </button>
            </div>
        </AuthenticatedLayout>
    );
}
