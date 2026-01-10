import React from "react";
// ...existing code...
import Slider from 'react-slick';
import { Button } from "../ui/Button";

import { ChevronLeft } from "lucide-react";
import { ChevronRight } from "lucide-react";

const stateLives = [
    {
        name: "Jeunes",
        description: "Groupe dédié aux jeunes membres de la communauté, avec des enseignements et activités spécifiques adaptés à cette tranche d'âge.",
        image: 'jeunes.jpeg'
    },
    {
        name: "Couples",
        description: "Espace consacré aux couples mariés ou fiancés, proposant un accompagnement spirituel et des ressources pour la vie conjugale.",
        image: 'couple.jpg'
    },
    {
        name: "Clercs",
        description: "Section réservée aux membres clercs de la communauté (prêtres, diacres), avec des contenus spécifiques à leur ministère.",
        image: 'clercs.jpg'
    },
    {
        name: "Laïcs",
        description: "Espace pour les membres laïcs de la communauté, proposant des ressources pour vivre leur foi dans le monde séculier.",
        image: 'jeunes.png'
    },
    {
         name: "Consacrés hommes",
         description: "Section dédiée aux hommes consacrés de la communauté, avec des enseignements et actualités propres à leur état de vie.",
         image: 'clercs.jpg'
    },
    {
       name: "Consacrées Femmes",
       description: "Espace réservé aux femmes consacrées de la communauté, proposant des contenus spécifiques à leur vocation religieuse.",
       image: 'femme.jpg'
    },
    {
        name: "Engagés perpétuels",
        description: "Section pour les membres ayant prononcé un engagement perpétuel dans la communauté, avec des ressources pour leur cheminement.",
        image: 'media2.jpeg'
    },
    {
        name: "Fraternité Sacerdotale de CANA",
        description: "Groupe spécifique de la fraternité sacerdotale au sein de la communauté CANA.",
        image: 'fraternite.jpeg'
    }
]

const PrevArrow = (props: any) => {
  const { className, style, onClick } = props;
  return (
    <div
      className={`${className} flex! items-center! justify-center! w-10! h-10!  rounded-full border! border-black! z-10 -right-10! before:hidden`}
      style={style}
      onClick={onClick}
    >
      <span className="text-black text-xl font-bold"><ChevronLeft/></span>
    </div>
  );
};

const NextArrow = (props: any) => {
  const { className, style, onClick } = props;
  return (
    <div
      className={`${className} flex! items-center! justify-center! w-10! h-10!  rounded-full border! border-black! z-10 -right-10! before:hidden`}
      style={style}
      onClick={onClick}
    >
      <span className="text-black text-xl font-bold"><ChevronRight/></span>
    </div>
  );
};

export default function StateOfLiveSlider() {
  var settings = {
    dots: true,
    infinite: true,
    speed: 500,
    slidesToShow: 3,
    slidesToScroll: 3,
    autoplay: true,
    autoplaySpeed: 5000,
    pauseOnHover: true,
    adaptiveHeight: true,
    // IMPORTANT: Ajouter les flèches personnalisées ici
    nextArrow: <NextArrow />,
    prevArrow: <PrevArrow />,
  };

  return (
    <div className="slider-container relative px-12"> {/* Ajoutez du padding pour laisser de l'espace aux flèches */}
      <Slider {...settings}>
        {stateLives.map((item, index) => (
          <div key={index} className='h-137.5 space-y-4 p-8 flex flex-col justify-between! cursor-pointer'>
            <h2 className='text-[#274B9C] text-xl font-bold'>{item.name}</h2>
            <p className='text-lg'>{item.description}</p>
            <Button className="w-37.5">
              Découvrir
            </Button>
            <div
                className="w-full h-80 bg-cover bg-center rounded-lg"
                style={{
                    backgroundImage: `url(${item.image})`
                }}
                >
            </div>
          </div>
        ))}
      </Slider>
    </div>
  );
}
