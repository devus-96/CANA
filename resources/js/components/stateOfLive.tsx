import React from "react";
// ...existing code...
import Slider from 'react-slick';
import { Button } from "./ui/Button";

const stateLives = [
    {
        name: "Jeunes",
        description: "Groupe dédié aux jeunes membres de la communauté, avec des enseignements et activités spécifiques adaptés à cette tranche d'âge.",
        image: 'jeunes.png'
    },
    {
        name: "Couples",
        description: "Espace consacré aux couples mariés ou fiancés, proposant un accompagnement spirituel et des ressources pour la vie conjugale.",
        image: 'jeunes.png'
    },
    {
        name: "Clercs",
        description: "Section réservée aux membres clercs de la communauté (prêtres, diacres), avec des contenus spécifiques à leur ministère.",
        image: 'jeunes.png'
    },
    {
        name: "Laïcs",
        description: "Espace pour les membres laïcs de la communauté, proposant des ressources pour vivre leur foi dans le monde séculier.",
        image: 'jeunes.png'
    },
    {
         name: "Consacrés hommes",
         description: "Section dédiée aux hommes consacrés de la communauté, avec des enseignements et actualités propres à leur état de vie.",
         image: 'jeunes.png'
    },
    {
       name: "Consacrées Femmes",
       description: "Espace réservé aux femmes consacrées de la communauté, proposant des contenus spécifiques à leur vocation religieuse.",
       image: 'jeunes.png'
    },
    {
        name: "Engagés perpétuels",
        description: "Section pour les membres ayant prononcé un engagement perpétuel dans la communauté, avec des ressources pour leur cheminement.",
        image: 'jeunes.png'
    },
    {
        name: "Fraternité Sacerdotale de CANA",
        description: "Groupe spécifique de la fraternité sacerdotale au sein de la communauté CANA.",
        image: 'jeunes.png'
    }
]

// Composants personnalisés pour les flèches
const NextArrow = (props) => {
  const { className, style, onClick } = props;
  return (
    <div
      className={`${className} custom-arrow next-arrow`}
      style={{ ...style, display: "block", right: "-25px" }}
      onClick={onClick}
    >
      ➔
    </div>
  );
};

const PrevArrow = (props) => {
  const { className, style, onClick } = props;
  return (
    <div
      className={`${className} custom-arrow prev-arrow text-black!`}
      style={{ ...style, display: "block", left: "-25px" }}
      onClick={onClick}
    >
      ←
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
    arrows: true,
    autoplay: true,
    autoplaySpeed: 5000,
    pauseOnHover: true,
    adaptiveHeight: true,
    nextArrow: <NextArrow />,
    prevArrow: <PrevArrow />,
  };
  return (
    <div className="slider-container">
    <Slider {...settings}>
      {stateLives.map((item, index) => (
            <div key={index} className='w-[800px] bg-red- p-8 flex flex-col justify-between hover:bg-black/10 cursor-pointer'>
                <h2 className='text-[#274B9C] text-xl font-bold'>{item.name}</h2>
                <p className='text-lg'>{item.description}</p>
                <Button className="w-37.5">
                    Decouvrir
                </Button>
                <div className={`w-full h-50 bg-[url(/jeunes.png)] rounded-lg`}>
                </div>
            </div>
        ))}
    </Slider>
    </div>
  );
}
