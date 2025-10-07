import React, {useEffect, useState} from 'react';
import defaultLogo from '../assets/logo.png';

const Logo = () => {
    const [logoUrl, setLogoUrl] = useState(null);
    const API_URL =  import.meta.env.VITE_API_URL;
    const fetchSettings = async () => {

       setLogoUrl(defaultLogo);

    };

    useEffect(() => {
        fetchSettings();
    }, []);

    if (!logoUrl) {
        return <div>Loading...</div>;
    }

    return (
        <img src={logoUrl} alt="Logo" style={{ maxWidth: '125px' }} />
    );
};

export default Logo;
