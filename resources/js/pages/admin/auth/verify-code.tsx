import { FormEventHandler } from 'react';
import { Head, usePage } from '@inertiajs/react';

import { InputField } from '@/components/form';
import TextLink from '@/components/typography/text-link';
import { Button } from '@/components/ui/Button';
import { useSimpleForm } from '@/hooks/use-simple-form';
import AuthLayout from '@/layouts/auth-layout';

type ForgotPasswordForm = {
    code: string;
    email: string
};

interface ForgotPasswordProps {
    status?: number;
}

export default function VerifyCode({ status }: ForgotPasswordProps) {
    const { auth } = usePage<any>().props;
    const { data, setData, post, processing, errors } = useSimpleForm<ForgotPasswordForm>({
        code: '',
        email: auth.email
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('admin.connexion'));
    };

    return (
        <AuthLayout>
            <Head title="Mot de passe oublié" />

            <h3 className="mt-8 text-center text-3xl font-semibold">Code de verification 2FA.</h3>
            <h4 className="mt-1 text-center text-gray-700">
                s'il vous plait saisissez le code envoyer a votre a adresse mail.
            </h4>

            {status && <div className="mt-6 mb-4 rounded-md bg-green-50 p-3 text-center text-sm font-medium text-green-600">{status}</div>}

            <div className="mt-6 space-y-6">
                <InputField
                    label="Verify code"
                    name="code"
                    value={data.code}
                    onChange={(e) => setData('code', e.target.value)}
                    error={errors.code}
                    type="number"
                    autoFocus
                    placeholder='XXXXXX'
                />
            </div>

            <div className="mt-8">
                <Button className="w-full" loading={processing} onClick={submit}>
                   se connecter
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
