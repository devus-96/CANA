import React from 'react';
import { Facebook, Twitter, Instagram, Youtube, Mail, Phone, MapPin } from 'lucide-react';

interface FooterLink {
  name: string;
  href: string;
}

interface FooterSection {
  title: string;
  links: FooterLink[];
}

const Footer: React.FC = () => {
  const quickLinks: FooterSection = {
    title: "Liens rapides",
    links: [
      { name: "Histoire", href: "/histoire" },
      { name: "Mission", href: "/mission" },
      { name: "États de vie", href: "/etats-de-vie" },
      { name: "Événements", href: "/evenements" },
      { name: "Enseignements", href: "/enseignements" },
    ]
  };

  const resources: FooterSection = {
    title: "Ressources",
    links: [
      { name: "Lectures du jour", href: "/lectures" },
      { name: "Médiathèque", href: "/mediatheque" },
      { name: "Intercession 24h/24", href: "/intercession" },
      { name: "Notre Fondateur", href: "/fondateur" },
      { name: "Saints Patrons", href: "/saints-patrons" },
    ]
  };

  const contactInfo = {
    phone: "237 677606169",
    email: "contact@cana.africa",
    socialMedia: [
      { icon: Facebook, href: "https://facebook.com/cmcana", label: "Facebook" },
      { icon: Twitter, href: "https://twitter.com/cmcana", label: "Twitter" },
      { icon: Instagram, href: "https://instagram.com/cmcana", label: "Instagram" },
      { icon: Youtube, href: "https://youtube.com/cmcana", label: "YouTube" },
    ]
  };

  const legalLinks = [
    { name: "Mentions légales", href: "/mentions-legales" },
    { name: "Confidentialité", href: "/confidentialite" },
  ];

  return (
    <footer className="w-full bg-gray-900 text-white pt-12 pb-8 mt-16">
      <div className="container mx-auto px-4 max-w-375">
        {/* Top Section */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
          {/* Brand Section */}
          <div className="space-y-6">
            <div className="space-y-4">
              <h2 className="text-2xl font-bold text-white">
                CMCana
              </h2>
              <p className="text-gray-300 text-lg leading-relaxed">
                Une communauté missionnaire au service de l'Évangile et de l'Église.
              </p>
            </div>

            {/* Social Media */}
            <div className="space-y-4">
              <h3 className="text-lg font-semibold text-white">Nous suivre</h3>
              <div className="flex gap-4">
                {contactInfo.socialMedia.map((social) => (
                  <a
                    key={social.label}
                    href={social.href}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="p-2 bg-gray-800 hover:bg-blue-600 rounded-full transition-all duration-300 hover:scale-110"
                    aria-label={social.label}
                  >
                    <social.icon className="w-5 h-5" />
                  </a>
                ))}
              </div>
            </div>
          </div>

          {/* Quick Links */}
          <div>
            <h3 className="text-xl font-semibold mb-6 pb-2 border-b border-blue-600 inline-block">
              {quickLinks.title}
            </h3>
            <ul className="space-y-3">
              {quickLinks.links.map((link) => (
                <li key={link.name}>
                  <a
                    href={link.href}
                    className="text-gray-300 hover:text-white hover:translate-x-2 transition-all duration-300 flex items-center gap-2 group"
                  >
                    <span className="w-1 h-1 bg-blue-500 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"></span>
                    {link.name}
                  </a>
                </li>
              ))}
            </ul>
          </div>

          {/* Resources */}
          <div>
            <h3 className="text-xl font-semibold mb-6 pb-2 border-b border-blue-600 inline-block">
              {resources.title}
            </h3>
            <ul className="space-y-3">
              {resources.links.map((link) => (
                <li key={link.name}>
                  <a
                    href={link.href}
                    className="text-gray-300 hover:text-white hover:translate-x-2 transition-all duration-300 flex items-center gap-2 group"
                  >
                    <span className="w-1 h-1 bg-blue-500 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"></span>
                    {link.name}
                  </a>
                </li>
              ))}
            </ul>
          </div>

          {/* Contact */}
          <div className="space-y-6">
            <h3 className="text-xl font-semibold mb-6 pb-2 border-b border-blue-600 inline-block">
              Contact
            </h3>
            <div className="space-y-4">
              <a
                href={`tel:${contactInfo.phone}`}
                className="flex items-center gap-3 text-gray-300 hover:text-white transition-colors"
              >
                <div className="p-2 bg-blue-600 rounded-lg">
                  <Phone className="w-5 h-5" />
                </div>
                <span>{contactInfo.phone}</span>
              </a>

              <a
                href={`mailto:${contactInfo.email}`}
                className="flex items-center gap-3 text-gray-300 hover:text-white transition-colors"
              >
                <div className="p-2 bg-blue-600 rounded-lg">
                  <Mail className="w-5 h-5" />
                </div>
                <span className="break-all">{contactInfo.email}</span>
              </a>

              <div className="flex items-start gap-3 text-gray-300">
                <div className="p-2 bg-blue-600 rounded-lg mt-1">
                  <MapPin className="w-5 h-5" />
                </div>
                <div>
                  <p className="font-medium">Cameroun</p>
                  <p className="text-sm">Communauté Missionnaire CANA</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Divider */}
        <div className="border-t border-gray-800 my-8"></div>

        {/* Bottom Section */}
        <div className="flex flex-col md:flex-row justify-between items-center gap-4">
          <div className="text-center md:text-left">
            <p className="text-gray-400">
              © {new Date().getFullYear()} Communauté Missionnaire CANA. Tous droits réservés.
            </p>
          </div>

          <div className="flex flex-wrap justify-center gap-6">
            {legalLinks.map((link) => (
              <a
                key={link.name}
                href={link.href}
                className="text-gray-400 hover:text-white transition-colors text-sm"
              >
                {link.name}
              </a>
            ))}
          </div>
        </div>

        {/* Back to Top */}
        <button
          onClick={() => window.scrollTo({ top: 0, behavior: 'smooth' })}
          className="fixed bottom-8 right-8 p-3 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg transition-all duration-300 hover:scale-110 z-50"
          aria-label="Retour en haut"
        >
          <svg
            className="w-6 h-6"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M5 10l7-7m0 0l7 7m-7-7v18"
            />
          </svg>
        </button>
      </div>
    </footer>
  );
};

export default Footer;
