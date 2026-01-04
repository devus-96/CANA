import AuthLayout from '@/layouts/auth-layout';
import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { ChangeEvent, FormEventHandler } from 'react';

import { InputField } from '@/components/form';
import { Button } from '@/components/ui/Button';

interface ResetPasswordProps {
    token: string;
    email: string;
}

type ResetPasswordForm = {
    token: string;
    email: string;
    password: string;
    password_confirmation: string;
};

export default function ResetPassword({ token, email }: ResetPasswordProps) {
    const { data, setData, post, processing, errors, reset } = useForm<Required<ResetPasswordForm>>({
        token: token,
        email: email,
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('password.store'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    const handleChange = (e: ChangeEvent<HTMLInputElement>) => {
        const { name, value } = e.target;
        setData(name as keyof ResetPasswordForm, value);
    };

    return (
        <AuthLayout>
            <Head title="Réinitialiser le mot de passe" />

            <div className="mx-auto h-25 w-25 bg-cover" style={{ backgroundImage: `url(/images/logo-1.jpeg)` }}></div>

            <h3 className="mt-8 text-center text-3xl font-semibold">Réinitialiser votre mot de passe</h3>
            <h4 className="mt-1 text-center text-gray-700">Veuillez entrer votre nouvelle adresse e-mail et votre nouveau mot de passe.</h4>

            <form onSubmit={submit} className="mt-6">
                <div className="space-y-6">
                    <InputField
                        inputClass="bg-gray-200"
                        label="Addresse email"
                        name="email"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        error={errors.email}
                        type="email"
                        readOnly
                        disabled
                    />

                    <InputField
                        label="Nouveau mot de passe"
                        name="password"
                        value={data.password}
                        onChange={handleChange}
                        error={errors.password}
                        canToggleType={true}
                        type="password"
                        autoFocus
                    />

                    <InputField
                        label="Confirmer le mot de passe"
                        name="password_confirmation"
                        value={data.password_confirmation}
                        onChange={handleChange}
                        error={errors.password_confirmation}
                        canToggleType={true}
                        type="password"
                    />
                </div>

                <div className="mt-8">
                    <Button className="w-full" loading={processing} type="submit">
                        {processing ? <LoaderCircle className="mr-2 h-4 w-4 animate-spin" /> : null}
                        Réinitialiser le mot de passe
                    </Button>
                </div>
            </form>
        </AuthLayout>
    );
}
