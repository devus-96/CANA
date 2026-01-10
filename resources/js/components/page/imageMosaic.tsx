import React from "react";

import Slider from 'react-slick';
import { Button } from "../ui/Button";

export default function ImageMosaic() {
  var settings = {
    infinite: true,
    speed: 6000,
    slidesToShow: 3,
    slidesToScroll: 3,
    autoplay: true,
    autoplaySpeed: 0,
    cssEase: 'linear',
    pauseOnHover: true,
    adaptiveHeight: true,
    arrows: false,
    dots: false,
  };

  return (
    <div className="slider-container relative px-12 "> {/* Ajoutez du padding pour laisser de l'espace aux flèches */}
      <Slider {...settings}>
           <div className='shrink-0  p-8'>
                <div className="w-100 h-100 rounded-lg" style={{
                    backgroundImage: `url(1.jpeg)`
                }}>

                </div>
            </div>
            <div className='shrink-0 p-8'>
                <div className="w-100 h-100 rounded-lg" style={{
                    backgroundImage: `url(2.jpeg)`
                }}>

                </div>
            </div>
            <div className='shrink-0 p-8'>
                <div className="w-100 h-100 rounded-lg" style={{
                    backgroundImage: `url(3.jpeg)`
                }}>

                </div>
            </div>
            <div className='shrink-0 p-8' >
                <div className="w-100 h-100 rounded-lg" style={{
                    backgroundImage: `url(2.jpeg)`
                }}>

                </div>
            </div>
      </Slider>
    </div>
  );
}
