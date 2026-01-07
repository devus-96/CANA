import { Head, useForm } from '@inertiajs/react';
import { ChangeEvent, FormEventHandler } from 'react';

import { InputField } from '@/components/form';
import TextLink from '@/components/typography/text-link';
import { Button } from '@/components/ui/Button';
import AuthLayout from '@/layouts/auth-layout';
import { useSimpleForm } from '@/hooks/use-simple-form';

type LoginForm = {
    email: string;
    password: string;
};

export default function Login() {

    const { data, post, processing, errors, reset, handleChange } = useSimpleForm<Required<LoginForm>>({
        email: '',
        password: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('admin.login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <AuthLayout>
            <Head title="Log in" />

            <h3 className="mt-8 text-center text-3xl font-semibold">Bienvenue !</h3>
            <h4 className="mt-1 text-center text-gray-700">Connectez-vous pour accéder à votre compte</h4>

            <form onSubmit={submit} className="mt-6 space-y-6">
                <InputField label="Adresse email" name="email" value={data.email} onChange={handleChange} error={errors.email} />

                <InputField
                    label="Mot de passe"
                    name="password"
                    value={data.password}
                    onChange={handleChange}
                    canToggleType={true}
                    type="password"
                    error={errors.password}
                />

                <div className="mt-4 text-sm text-gray-700">
                    <TextLink href='' className="ml-auto text-sm" tabIndex={5}>
                        <span>Vous avez oublié votre mot de passe ?</span>
                        <span className="ml-1 text-teal-800 hover:underline">{`Oui j'ai oublié mon mot de passe`}</span>
                    </TextLink>
                </div>

                <div className="mt-8">
                    <Button className="w-full" loading={processing} type="submit">
                        Se connecter
                    </Button>
                </div>

                <div className="text-muted-foreground mt-4 text-center text-sm">
                    <TextLink href={'/auth/register'}  tabIndex={5}>
                        <span>Vous n'avez pas de compte ?</span>
                        <span className="ml-1 text-teal-800 hover:underline">{`Créer un compte`}</span>
                    </TextLink>
                </div>
            </form>
        </AuthLayout>
    );
}
