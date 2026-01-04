import { Head } from '@inertiajs/react';
import { FormEventHandler } from 'react';

import { InputField } from '@/components/form';
import TextLink from '@/components/typography/text-link';
import { Button } from '@/components/ui/Button';
import { useSimpleForm } from '@/hooks/use-simple-form';
import AuthLayout from '@/layouts/auth-layout';

type RegisterForm = {
    first_name: string;
    last_name: string;
    gender: string;
    date_of_birth: string;
    city: string;
    email: string;
    password: string;
    password_confirmation: string;
};

export default function Register() {
    const { data, setData, post, processing, errors, reset, handleChange } = useSimpleForm<Required<RegisterForm>>({
        first_name: '',
        last_name: '',
        gender: '',
        date_of_birth: new Date().toISOString().split('T')[0],
        city: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <AuthLayout>
            <Head title="Créer un compte" />
            <form className="" onSubmit={submit}>
                <div className="mx-auto h-25 w-25 bg-cover"></div>

                <h3 className="mt-6 text-center text-2xl font-semibold">Rejoignez-nous dès aujourd’hui !</h3>
                <h4 className="mt-1 text-center text-gray-700">Créez votre compte en quelques secondes et devenez menbre de la communaute CANA</h4>

                <div className="mt-6 space-y-4">
                    <InputField label="first name *" name="first_name" value={data.first_name} onChange={handleChange} error={errors.first_name} />
                    <InputField label="last name" name="last_name" value={data.last_name} onChange={handleChange} error={errors.last_name} />

                    <InputField label="Adresse email *" name="email" value={data.email} onChange={handleChange} error={errors.email} />

                     <InputField
                        type='date'
                        label="Date of birth"
                        name="date_of_birth"
                        value={data.date_of_birth}
                        onChange={handleChange}
                        error={errors.date_of_birth}
                    />

                    <InputField label="City" name="city" value={data.city} onChange={handleChange} error={errors.city} />

                    <InputField
                        label="Mot de passe *"
                        name="password"
                        value={data.password}
                        onChange={handleChange}
                        canToggleType={true}
                        type="password"
                        error={errors.password}
                    />

                    <InputField
                        label="Confirmer le mot de passe *"
                        name="password_confirmation"
                        value={data.password_confirmation}
                        onChange={handleChange}
                        canToggleType={true}
                        type="password"
                        error={errors.password_confirmation}
                    />

                    <Button className="mt-6 w-full" loading={processing} type="submit">
                        Créer un compte
                    </Button>
                </div>

                <div className="text-muted-foreground mt-6 text-center text-sm">
                    Vous avez déjà un compte ?{' '}
                    <TextLink  href='/auth/login'  tabIndex={6}>
                        Se connecter
                    </TextLink>
                </div>
            </form>
        </AuthLayout>
    );
}
