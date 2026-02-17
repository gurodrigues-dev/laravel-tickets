import { useState, useEffect } from 'react';
import GuestLayout from '@/Layouts/GuestLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Head, Link, router } from '@inertiajs/react';
import { register, login, getFieldValidationError } from '@/utils/auth';

export default function Register() {
    const [data, setData] = useState({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });
    const [processing, setProcessing] = useState(false);
    const [errors, setErrors] = useState({});
    const [apiError, setApiError] = useState('');

    useEffect(() => {
        return () => {
            setData(prev => ({
                ...prev,
                password: '',
                password_confirmation: ''
            }));
        };
    }, []);

    const submit = async (e) => {
        e.preventDefault();
        setProcessing(true);
        setApiError('');
        setErrors({});

        // First register the user
        const registerResult = await register(data);

        if (!registerResult.success) {
            setProcessing(false);

            if (registerResult.validationErrors) {
                setErrors(registerResult.validationErrors);
            } else {
                setApiError(registerResult.error);
            }
            return;
        }

        // After successful registration, attempt auto-login
        const loginResult = await login(data.email, data.password);

        setProcessing(false);

        if (loginResult.success) {
            // Redirect to dashboard on successful login
            router.visit(route('dashboard'), {
                method: 'get',
            });
        } else {
            // Registration succeeded but auto-login failed, redirect to login page
            router.visit(route('login'), {
                method: 'get',
                data: { registered: true, email: data.email }
            });
        }
    };

    const onHandleChange = (event) => {
        setData(prev => ({
            ...prev,
            [event.target.name]: event.target.value
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
            <Head title="Register" />

            <form onSubmit={submit}>
                {apiError && (
                    <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-md">
                        <p className="text-sm text-red-600">{apiError}</p>
                    </div>
                )}

                <div>
                    <InputLabel htmlFor="name" value="Name" />

                    <TextInput
                        id="name"
                        name="name"
                        value={data.name}
                        className="mt-1 block w-full"
                        autoComplete="name"
                        isFocused={true}
                        onChange={onHandleChange}
                        required
                    />

                    <InputError message={getFieldValidationError(errors, 'name') || errors.name} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="email" value="Email" />

                    <TextInput
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        className="mt-1 block w-full"
                        autoComplete="username"
                        onChange={onHandleChange}
                        required
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
                        autoComplete="new-password"
                        onChange={onHandleChange}
                        required
                    />

                    <InputError message={getFieldValidationError(errors, 'password') || errors.password} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="password_confirmation" value="Confirm Password" />

                    <TextInput
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        value={data.password_confirmation}
                        className="mt-1 block w-full"
                        autoComplete="new-password"
                        onChange={onHandleChange}
                        required
                    />

                    <InputError message={getFieldValidationError(errors, 'password_confirmation') || errors.password_confirmation} className="mt-2" />
                </div>

                <div className="flex items-center justify-end mt-4">
                    <Link
                        href={route('login')}
                        className="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Already registered?
                    </Link>

                    <PrimaryButton className="ms-4" disabled={processing}>
                        {processing ? 'Creating account...' : 'Register'}
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
