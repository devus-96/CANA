import React from "react";
import { Button } from "../ui/Button";
import { MapPin } from "lucide-react";

const events = [
    {
        day: 28,
        date: 'juin 2026',
        name: "Night of Player and Workship with simon jones You Must Kwow God's design",
        location: "Douala, Akwa",
        start_time: 'Thursday, 8:00AM - 5:00PM'
    },
    {
        day: 28,
        date: 'juin 2026',
        name: "Night of Player and Workship with simon jones You Must Kwow God's design",
        location: "Douala, Akwa",
        start_time: 'Thursday, 8:00AM - 5:00PM'
    },
    {
        day: 28,
        date: 'juin 2026',
        name: "Night of Player and Workship with simon jones You Must Kwow God's design",
        location: "Douala, Akwa",
        start_time: 'Thursday, 8:00AM - 5:00PM'
    },
    {
        day: 28,
        date: 'juin 2026',
        name: "Night of Player and Workship with simon jones You Must Kwow God's design",
        location: "Douala, Akwa",
        start_time: 'Thursday, 8:00AM - 5:00PM'
    }
]


export default function Events () {
    return (
        <div className='w-full relative z-10 max-w-375'>
            <div className='w-full flex justify-between items-center'>
                <div>
                    <h1 className='text-4xl font-bold'>Upcoming Events</h1>
                    <p className='text-xl'>we have a strond sense of community with parishioners</p>
                </div>
                <Button className="w-fit bg-transparent! text-white! border">
                    Voir le calendrier
                </Button>
            </div>


                <div className='mt-12'>
                {events.map((item, index) => (
                    <div key={index} className='flex justify-between items-center gap-8 border-b border-white/60 py-4'>
                        <div className='bg-[#274B9C] px-4 py-6 text-center'>
                            <p>{item.day}</p>
                            <p>{item.date}</p>
                        </div>
                        <div className='w-[500px]'>
                            <p className='text-xl'>{item.name}</p>
                        </div>
                        <div className='flex gap-4'>
                            <MapPin />
                            <div>
                                <p>{item.start_time}</p>
                                <p>{item.location}</p>
                            </div>
                        </div>
                        <div className='space-x-8'>
                            <Button className="w-fit bg-transparent! border rounded-none">
                                En savoir plus
                            </Button>
                            <Button className="w-37.5 rounded-none! bg-[#274B9C]!">
                                Reserver
                            </Button>
                        </div>

                    </div>
                ))}
                </div>
        </div>
    )
}
