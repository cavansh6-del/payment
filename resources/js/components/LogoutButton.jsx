import React, { useState, useEffect } from 'react';
import { Row, Col, Button } from 'antd';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
const LogoutButton  = () => {
    const { t } = useTranslation();
    const navigate = useNavigate();

    const logoutHandler = () => {
        localStorage.removeItem('queryParams');
        navigate('/');
    };


    return (
        <Button type="default" variant="outlined" color="danger" block onClick={logoutHandler}>
            {t('logout')}
        </Button>
    );
};

export default LogoutButton ;
