import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { topbar } from '@/constant/topbar';
import { Button } from '@/components/ui/Button';
import { ChevronRight, BookOpen, MapPin } from 'lucide-react';
import StateOfLiveSlider from '@/components/page/stateOfLive';
import Header from '@/components/page/header';
import Events from '@/components/page/event';



const articles = [
    {
        title: "L'amour dans le mariage chrétien",
        tags: "Couples",
        excerpt: "Je vous donne un commandement nouveau : c'est de vous aimer les uns les autres. ....",
        published_at: '18 Déc 2025'
    },
    {
        title: "L'amour dans le mariage chrétien",
        tags: "Couples",
        excerpt: "Je vous donne un commandement nouveau : c'est de vous aimer les uns les autres. ....",
        published_at: '18 Déc 2025'
    },
    {
        title: "L'amour dans le mariage chrétien",
        tags: "Couples",
        excerpt: "Je vous donne un commandement nouveau : c'est de vous aimer les uns les autres. ....",
        published_at: '18 Déc 2025'
    },
]

const podcasts = [
    {
        title: 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Aperiam perferendis velit.',
        author: 'John doe'
    },
    {
        title: 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Aperiam perferendis velit.',
        author: 'John doe'
    },
    {
        title: 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Aperiam perferendis velit.',
        author: 'John doe'
    }
]

export default function Welcome() {
  const { auth } = usePage<any>().props;

   console.log(auth);

    return (
        <>
            <Head title="Welcome">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>

            <div className='flex flex-col items-center bg-[#FDFDFC]  text-[#1b1b18]'>
                <Header />
            </div>

           <div className='relative w-full flex flex-col items-center mt-16'>
                <main className='relative w-full space-y-16 px-16 flex flex-col items-center'>

                    <div className='flex justify-between w-full space-x-8 max-w-375'>
                            <div className='w-1/2'>
                                <h1 className='text-4xl text-[#274B9C] font-bold'>We are church that believe in god. Everyone is welcome</h1>
                                <div className='text-xl space-y-4 mt-8'>
                                     <p>La Communauté Missionnaire CANA est une famille spirituelle fondée sur l'amour du Christ et le service de l'Église. Nous accueillons tous les états de vie:</p>
                                     <p>jeunes, couples, clercs, laïcs et consacrés.</p>
                                    <p>
                                    Notre mission est d'annoncer la Bonne Nouvelle, d'accompagner les personnes dans leur cheminement spirituel et de vivre en communion fraternelle selon l'Esprit de l'Évangile.
                                    </p>

                                   <Link
                                   className='flex items-center text-sm text-[#274B9C] font-bold'
                                   >
                                    <p>En savoir plus </p><ChevronRight />
                                   </Link>
                                </div>

                            </div>
                            <div className='w-1/2 h-[400px] relative rounded-xl flex items-center max-w-full bg-[url(/image2.png)] bg-no-repeat bg-cover bg-[position:20%_20%] px-24'>

                            </div>
                    </div>

                    <div className='w-full max-w-375'>
                        <h1 className='text-4xl text-center text-[#274B9C] font-bold'>Nourrir sa foi chaque jour</h1>
                        <p className='text-center text-xl mt-2 text-[#274B9C]'>Méditations et versets quotidiens</p>

                        <div className='grid grid-cols-2 gap-8 mt-8'>
                            <div className='shadow-xl p-8 space-y-4 border-l-4 border-[#6366f1]' >
                                <div className='flex items-center text-2xl space-x-4 text-[#274B9C] font-bold'>
                                    <p>Verset de jour</p>
                                </div>
                                <div className='relative italic text-xl leading-6 pl-12 before:absolute before:left-0 before:-top-2 before:text-6xl before:font-bold before:content-["\201C"] before:text-indigo-500 before:leading-none'>
                                    <p className=''>Je vous donne un commandement nouveau : c'est de vous aimer les uns les autres. Comme je vous ai aimés, vous aussi aimez-vous les uns les autres.</p>
                                </div>
                                <p>Jean 13:34</p>
                                 <Link
                                   className='flex items-center text-sm text-[#274B9C] font-bold'
                                   >
                                <p>Lire plus </p><ChevronRight size={16}/>
                                </Link>
                            </div>

                            <div className='shadow-xl p-8 space-y-4 border-l-4 border-[#6366f1]' >
                                <div className='flex items-center text-2xl space-x-4 text-[#274B9C] font-bold'>
                                    <p>Méditation du jour</p>
                                </div>
                                 <div className='relative italic text-xl leading-6 pl-12 before:absolute before:left-0 before:-top-2 before:text-6xl before:font-bold before:content-["\201C"] before:text-indigo-500 before:leading-none'>
                                    <p className=''>L'amour véritable se manifeste dans le don de soi. Chaque jour est une opportunité de témoigner de l'amour du Christ par nos actes et nos paroles...</p>
                                </div>
                                 <Link
                                   className='flex items-center text-sm text-[#274B9C] font-bold'
                                   >
                                <p>Lire plus </p><ChevronRight />
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div className='h-[800px] w-full relative flex justify-center text-[#ededed]  max-w-full bg-[url(/image3.png)] bg-no-repeat bg-cover bg-center p-16'>
                       <div className='absolute w-full h-full inset-0 bg-black/80'></div>
                       <Events />
                    </div>

                    <div className='w-full'>
                            <h1 className='text-4xl text-center text-[#274B9C] font-bold'>États de vie</h1>
                            <p className='text-center text-xl mt-2 text-[#274B9C] mb-4'>Tous les états de vie sont accueillis dans notre communauté</p>
                            <StateOfLiveSlider />
                    </div>

                    <div className='w-full'>
                        <div className='w-full flex justify-between items-center'>
                            <div>
                                <h1 className='text-4xl  text-[#274B9C] font-bold'>Enseignements récents</h1>
                                <p className='text-xl mt-2 text-[#274B9C]'>Nourrissez votre foi avec nos dernières réflexions</p>
                            </div>
                           <Link
                                className='flex items-center text-sm text-[#274B9C] font-bold'
                                >
                            <p>Tous les enseignements </p><ChevronRight />
                            </Link>
                        </div>

                        <div className='w-full grid grid-cols-3 gap-4'>
                                {articles.map((item, index) => (
                                    <div key={index} className='border rounded-lg'>
                                        <div className='w-full h-[200px]'>

                                        </div>
                                        <div className='p-4'>
                                            <div className='flex items-center'>
                                            <p>{item.tags}. {item.published_at}</p>
                                            </div>
                                            <p className='text-[#274B9C]'>{item.title}</p>
                                            <p className='text-xl mt-4'>{item.excerpt}</p>
                                            <Link
                                                className='flex items-center text-sm text-[#274B9C] font-bold'
                                                >
                                                <p>Lire plus </p><ChevronRight />
                                            </Link>
                                        </div>

                                    </div>
                                ))}
                        </div>
                    </div>

                    <div>
                        <div>
                            <h1 className='text-4xl text-center'>Médiathèque</h1>
                            <p className='text-center'>Vidéos, audios et photos de notre communauté</p>
                            </div>
                        </div>

                        <div className='grid grid-cols-4 gap-2'>
                            <img src="/media1.jpeg" alt="" className='rounded-xl' />
                            <img src="/media2.jpeg" alt="" className='rounded-xl' />
                            <img src="/media3.jpeg" alt=""  className='rounded-xl'/>
                            <img src="/media4.jpeg" alt="" className='rounded-xl' />
                            <img src="/media5.jpeg" alt="" className='rounded-xl'/>
                        </div>

                        <div>
                            <h1 className='text-xl'>Podcasts</h1>
                            <p>Listen podcasts of your chuch</p>
                        </div>

                        <div>
                            {podcasts.map((item, index) => (
                                <div>

                                </div>
                            ))}
                        </div>
                </main>
            </div>


        </>
    );
}
