import { useState, useEffect } from 'react';
import Checkbox from '@/Components/Checkbox';
import GuestLayout from '@/Layouts/GuestLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Head, Link, router } from '@inertiajs/react';
import { login, formatRateLimitMessage, getFieldValidationError } from '@/utils/auth';

export default function Login({ status, canResetPassword }) {
    const [data, setData] = useState({
        email: '',
        password: '',
        remember: false,
    });
    const [processing, setProcessing] = useState(false);
    const [errors, setErrors] = useState({});
    const [apiError, setApiError] = useState('');
    const [rateLimit, setRateLimit] = useState(null);

    useEffect(() => {
        return () => {
            setData(prev => ({ ...prev, password: '' }));
        };
    }, []);

    const submit = async (e) => {
        e.preventDefault();
        setProcessing(true);
        setApiError('');
        setErrors({});
        setRateLimit(null);

        const result = await login(data.email, data.password, data.remember);

        setProcessing(false);

        if (result.success) {
            // Redirect to intended destination or dashboard
            const intendedUrl = window.sessionStorage.getItem('intended_url');
            router.visit(intendedUrl || route('dashboard'), {
                method: 'get',
            });
        } else {
            // Handle errors
            if (result.validationErrors) {
                setErrors(result.validationErrors);
            }

            if (result.status === 429 && result.retryAfter) {
                setRateLimit(result.retryAfter);
                setApiError(formatRateLimitMessage(result.retryAfter));
            } else {
                setApiError(result.error);
            }
        }
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
                {apiError && (
                    <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-md">
                        <p className="text-sm text-red-600">{apiError}</p>
                        {rateLimit && (
                            <p className="text-xs text-red-500 mt-1">
                                Retry in: {Math.floor(rateLimit / 60)}:{(rateLimit % 60).toString().padStart(2, '0')}
                            </p>
                        )}
                    </div>
                )}

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

                    <InputError message={getFieldValidationError(errors, 'email') || errors.email} className="mt-2" />
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

                    <InputError message={getFieldValidationError(errors, 'password') || errors.password} className="mt-2" />
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

                    <PrimaryButton className="ms-4" disabled={processing || rateLimit > 0}>
                        {processing ? 'Logging in...' : 'Log in'}
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
