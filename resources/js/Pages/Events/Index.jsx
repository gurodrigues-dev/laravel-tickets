import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import axios from 'axios';

export default function Index() {
    const [events, setEvents] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        axios.get('/api/events')
            .then(res => {
                setEvents(res.data);
            })
            .catch(() => {
                alert("Erro ao carregar eventos");
            })
            .finally(() => setLoading(false));
    }, []);

    return (
        <AuthenticatedLayout>
            <Head title="Events" />

            <div className="p-6">
                <h1 className="text-2xl font-bold mb-4">Events</h1>

                {loading && <p>Loading...</p>}

                {!loading && events.map(event => (
                    <div key={event.id} className="border p-4 mb-4 rounded">
                        <h2 className="text-xl font-semibold">{event.name}</h2>
                        <p>{event.description}</p>
                        <p>Date: {new Date(event.event_date).toLocaleString()}</p>
                        <p>Available: {event.available_tickets}</p>

                        <button
                            className="mt-2 bg-blue-500 text-white px-4 py-2 rounded"
                            disabled={event.available_tickets === 0}
                            onClick={() => router.visit(`/reservations/create/${event.id}`)}
                        >
                            Reserve
                        </button>
                    </div>
                ))}
            </div>
        </AuthenticatedLayout>
    );
}
