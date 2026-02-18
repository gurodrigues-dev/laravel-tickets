import { useState, useEffect } from 'react';
import Checkbox from '@/Components/Checkbox';
import GuestLayout from '@/Layouts/GuestLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Head, Link, router } from '@inertiajs/react';

export default function Login({ status, canResetPassword }) {
    const [data, setData] = useState({
        email: '',
        password: '',
        remember: false,
    });
    const [processing, setProcessing] = useState(false);
    const [errors, setErrors] = useState({});

    useEffect(() => {
        return () => {
            setData(prev => ({ ...prev, password: '' }));
        };
    }, []);

    const submit = (e) => {
        e.preventDefault();
        setProcessing(true);

        router.post('/login', data, {
            onFinish: () => setProcessing(false),
            onError: (errors) => {
                setErrors(errors);
            },
            onSuccess: () => {
            }
        });
    };

    const onHandleChange = (event) => {
        setData(prev => ({
            ...prev,
            [event.target.name]: event.target.type === 'checkbox' ? event.target.checked : event.target.value
        }));
        // Clear field error when user types
        if (errors[event.target.name]) {
            setErrors(prev => {
                const newErrors = { ...prev };
                delete newErrors[event.target.name];
                return newErrors;
            });
        }
    };

    return (
        <GuestLayout>
            <Head title="Log in" />

            {status && <div className="mb-4 font-medium text-sm text-green-600">{status}</div>}

            <form onSubmit={submit}>
                <div>
                    <InputLabel htmlFor="email" value="Email" />

                    <TextInput
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        className="mt-1 block w-full"
                        autoComplete="username"
                        isFocused={true}
                        onChange={onHandleChange}
                    />

                    <InputError message={errors.email} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="password" value="Password" />

                    <TextInput
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        className="mt-1 block w-full"
                        autoComplete="current-password"
                        onChange={onHandleChange}
                    />

                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div className="block mt-4">
                    <label className="flex items-center">
                        <Checkbox
                            name="remember"
                            checked={data.remember}
                            onChange={onHandleChange}
                        />
                        <span className="ms-2 text-sm text-gray-600">Remember me</span>
                    </label>
                </div>

                <div className="flex items-center justify-end mt-4">
                    {canResetPassword && (
                        <Link
                            href={route('password.request')}
                            className="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Forgot your password?
                        </Link>
                    )}

                    <PrimaryButton className="ms-4" disabled={processing}>
                        {processing ? 'Logging in...' : 'Log in'}
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
