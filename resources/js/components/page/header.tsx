import React from "react";
import { Button } from "../ui/Button";
import { Link } from "@inertiajs/react";
import { topbar } from "@/constant/topbar";
import { ChevronRight, BookOpen, MapPin, Heart, ArrowRight } from 'lucide-react';


export default function Header () {
    return (
        <div className='w-full flex relative flex-col items-center bg-[url(/principal.png)] bg-no-repeat bg-cover bg-center '>
               <div className='absolute w-full h-full inset-0 bg-black/70'></div>
                <header className='w-full relative z-10 px-16'>
                    <div className="border-b flex justify-between items-center py-4">
                        <div className="text-white flex items-center">
                            <MapPin className="" />
                            <p>Akwa, Lorem ipsum dolor sit amet consectetur.</p>
                        </div>
                        <div className="flex">
                            <Button className="w-37.5 bg-transparent! rounded!">
                                Login
                            </Button>
                            <Button className="w-37.5 bg-white! text-[#274B9C]! rounded!">
                                Register
                            </Button>
                            <Button className="w-37.5 rounded!">
                                <Heart className="mr-2" />
                                Donate
                            </Button>
                        </div>
                    </div>
                    <div className="flex w-full justify-between items- py-8">
                        <img src='/CMCana.svg'></img>
                        <nav className='flex space-x-8'>
                                {topbar.map((item, index) => (
                                <span className='relative w-fit after:absolute after:w-0 after:h-1 after:rounded-lg after:left-0 after:bottom-1.25 after:duration-300 after:bg-white hover:after:w-full'>
                                    <Link
                                        key={index}
                                        href={item.route}
                                        className="uppercase inline-block rounded-sm border-[#19140035] py-1.5 leading-normal text-white/90 hover:border-[#1915014a] "
                                        >
                                        {item.name}
                                    </Link>
                                </span>

                                ))}
                        </nav>
                    </div>

                </header>
                <div className='w-full relative flex flex-col items-center mt-16 border-b py-16'>
                    <div className='text-white/90 space-y-8'>
                        <div className="font-semibold">
                            <h1 className='text-7xl uppercase text-center'>Communauté  </h1>
                            <h1 className='text-7xl uppercase text-center'>Missionnaire de cana</h1>
                        </div>
                        <div>
                            <p className="text-xl text-center text-white/80">Join us for church in Akwa, TX on Sundays at 08:00 . where you are in life. there is a place for church</p>
                            <p className='text-xl text-center text-white/80'>Une vie donnée au service de l'Évangile et de l'Église !</p>
                        </div>

                    </div>
                    <div className='flex items-center space-x-4'>
                        <Button className="w-75 uppercase! rounded! mt-8">
                            Learn more about us
                        </Button>
                    </div>
                </div>

                <div className="w-full text-white pt-16 pb-24 relative z-10 gap-8 px-8 flex justify-around bg-white/10">
                        <div className="">
                            <p className="text-4xl font-semibold text-center">+ 1000</p>
                            <p className="text-xl text-center text-white/70">Peoples in the church</p>
                        </div>
                        <div className="">
                            <p className="text-4xl font-semibold text-center">23</p>
                            <p className="text-xl text-white/70">Church pastors</p>
                        </div>
                        <div className="">
                            <p className="text-4xl font-semibold text-center">+ 98</p>
                            <p className="text-xl text-white/70 text-center">Church Events</p>
                        </div>
                        <div className="">
                            <p className="text-4xl font-semibold text-center">100%</p>
                            <p className="text-xl text-white/70">Satisfied peoples</p>
                        </div>
                </div>
            </div>
    )
}
