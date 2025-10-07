import React from 'react';
import { Result, Button } from 'antd';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';

const NoAccessPage = () => {
    const navigate = useNavigate();
    const { t, i18n } = useTranslation();

    const goHome = () => {
        navigate('/');
    };

    return (
        <div style={{ padding: '50px', display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh' }}>
            <Result
                status="403"
                title={t("accessDenied")}
                subTitle={t("dontPermission")}
            />
        </div>
    );
};

export default NoAccessPage;
