import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

import TextLink from '@/components/typography/text-link';
import { Button } from '@/components/ui/Button'; // Assuming Button component is available
import AuthLayout from '@/layouts/auth-layout';

export default function VerifyEmail({ status }: { status?: string }) {
    const { post, processing } = useForm({});

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('verification.send'));
    };

    return (
        <AuthLayout>
            <Head title="Vérification de l'e-mail" />

            {/* Logo and Titles similar to other auth pages */}
            <div className="mx-auto h-25 w-25 bg-cover" style={{ backgroundImage: `url(/images/logo-1.jpeg)` }}></div>

            <h3 className="mt-8 text-center text-3xl font-semibold">Vérifiez votre adresse e-mail</h3>
            <h4 className="mt-1 text-center text-gray-700">
                Merci de vous être inscrit ! Avant de commencer, pourriez-vous vérifier votre adresse e-mail en cliquant sur le lien que nous venons
                de vous envoyer ? Si vous n'avez pas reçu l'e-mail, nous vous en enverrons un autre avec plaisir.
            </h4>

            {status === 'verification-link-sent' && (
                <div className="mt-6 mb-4 rounded-md bg-green-50 p-3 text-center text-sm font-medium text-green-600">
                    Un nouveau lien de vérification a été envoyé à l'adresse e-mail que vous avez fournie lors de l'inscription.
                </div>
            )}

            <form onSubmit={submit} className="mt-6 space-y-6 text-center">
                <Button className="w-full" loading={processing} type="submit">
                    {processing ? <LoaderCircle className="mr-2 h-4 w-4 animate-spin" /> : null}
                    Renvoyer l'e-mail de vérification
                </Button>

                <TextLink href='' method="post" className="mx-auto block text-sm text-teal-800 hover:underline">
                    Se déconnecter
                </TextLink>
            </form>
        </AuthLayout>
    );
}
