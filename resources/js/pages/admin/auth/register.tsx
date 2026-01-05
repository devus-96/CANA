import { Head } from '@inertiajs/react';
import { FormEventHandler } from 'react';

import { InputField, InputPhoneField } from '@/components/form';
import TextLink from '@/components/typography/text-link';
import { Button } from '@/components/ui/Button';
import { useSimpleForm } from '@/hooks/use-simple-form';
import AuthLayout from '@/layouts/auth-layout';

type RegisterForm = {
    name: string;
    email: string;
    phone: string;
    password: string;
    password_confirmation: string;
};

export default function Register() {
    const { data, setData, post, processing, errors, reset, handleChange, setValue } = useSimpleForm<Required<RegisterForm>>({
        name: '',
        phone: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('admin.register'), {
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
                    <InputField label="Full name *" name="name" value={data.name} onChange={handleChange} error={errors.name} />

                    <InputField label="Adresse email *" name="email" value={data.email} onChange={handleChange} error={errors.email} />

                    <InputPhoneField
                        name='phone'
                        label='Phone Number'
                        value={data.phone}
                        onChange={setValue}
                        placeholder='Enter your MOMO or OM only !'
                    />

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
