import { Head } from '@inertiajs/react';
import { FormEventHandler } from 'react';

import { InputField } from '@/components/form';
import TextLink from '@/components/typography/text-link';
import { Button } from '@/components/ui/Button';
import { useSimpleForm } from '@/hooks/use-simple-form';
import AuthLayout from '@/layouts/auth-layout';

type ForgotPasswordForm = {
    email: string;
};

interface ForgotPasswordProps {
    status?: string;
}

export default function ForgotPassword({ status }: ForgotPasswordProps) {
    const { data, setData, post, processing, errors } = useSimpleForm<ForgotPasswordForm>({
        email: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('password.email'));
    };

    return (
        <AuthLayout>
            <Head title="Mot de passe oublié" />

            <div className="mx-auto h-25 w-25 bg-cover" style={{ backgroundImage: `url(/images/logo-1.jpeg)` }}></div>

            <h3 className="mt-8 text-center text-3xl font-semibold">Mot de passe oublié ?</h3>
            <h4 className="mt-1 text-center text-gray-700">
                Pas de problème. Indiquez-nous simplement votre adresse e-mail et nous vous enverrons un lien de réinitialisation de mot de passe qui
                vous permettra d'en choisir un nouveau.
            </h4>

            {status && <div className="mt-6 mb-4 rounded-md bg-green-50 p-3 text-center text-sm font-medium text-green-600">{status}</div>}

            <div className="mt-6 space-y-6">
                <InputField
                    label="Addresse email"
                    name="email"
                    value={data.email}
                    onChange={(e) => setData('email', e.target.value)}
                    error={errors.email}
                    type="email"
                    autoFocus
                />
            </div>

            <div className="mt-8">
                <Button className="w-full" loading={processing} onClick={submit}>
                    Envoyer le lien de réinitialisation
                </Button>
            </div>

            <div className="text-muted-foreground mt-4 text-center text-sm">
                <TextLink href='' tabIndex={5}>
                    <span>Retour à la connexion</span>
                </TextLink>
            </div>
        </AuthLayout>
    );
}
