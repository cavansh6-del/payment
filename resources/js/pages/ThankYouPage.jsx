import React, { useEffect, useState } from 'react';
import { Card, Row, Col, Table, Typography, Button, Layout, Spin } from 'antd';
import { useLocation,useNavigate } from "react-router-dom";
import { useTranslation } from 'react-i18next';
import { ArrowLeftOutlined, LoadingOutlined } from '@ant-design/icons';
import Logo from "@/components/Logo.jsx";
import { checkPayment, createPayment } from '@/services/api.js';

const antIcon =   <LoadingOutlined style={{fontSize: 24, color: '#7230ff',}} spin/>;

const { Title, Text } = Typography;
const { Header, Content } = Layout;

export default function ThankYou() {
    const [pageLoading, setPageLoading] = useState(true);
    const { t } = useTranslation();
    const location = useLocation();
    const navigate = useNavigate();

    const [userComment, setUserComment] = useState("");
    const [orderData, setOrderData] = useState(location.state || {});
    const [user, setUser] = useState(null);
    const [products, setProducts] = useState(null);
    const [externalOrderNumber, setExternalOrderNumber] = useState(0);
    const [orderStatus, setOrderStatus] = useState("");
    const [total, setTotal] = useState(0);

    const currentLang = 'en';
    const goToProducts = () => {
        navigate("/");
    };


    useEffect(() => {
        sessionStorage.removeItem("orderData");
        const searchParams = new URLSearchParams(window.location.search);
        const token = searchParams.get('token');

        if (!token) {
            navigate('/');
            return;
        }

        const fetchOrder = async () => {
            try {
                const response = await checkPayment(token); // فرض بر اینکه checkPayment یک promise برمی‌گرداند
                console.log(response);

                if (response && response.data.status) {
                    setOrderStatus(response.data.status);
                    setTotal(response.data.total_amount);
                    setExternalOrderNumber(response.data.id);
                    setProducts([response.data]);
                }
            } catch (err) {
                console.error(err);
            } finally {
                setPageLoading(false);
            }
        };

        fetchOrder();

    }, []);


    const formattedDate = new Date().toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    const columns = [
        {
            title: t("name"),
            dataIndex: "product",
            key: "name",
            render: (value) => `${value.name}`,
        },
        {
            title: t("description"),
            dataIndex: "product",
            key: "description",
            align: "center",
            render: (value) => `${value.description}`,
        },
        {
            title: t("subtotal"),
            dataIndex: "total_amount",
            key: "total_amount",
            align: "right",
            render: (value) => `$${value}`,
        },
    ];

    if (pageLoading) {
        return (
            <div style={{
                height: '100vh',
                width: '100%',
                display: 'flex',
                justifyContent: 'center',
                alignItems: 'center',
            }}>
                <Spin indicator={antIcon} />
            </div>
        );
    } else {
        let statusMsg = null;
        if (orderStatus === 'completed') {
            statusMsg = (
                <Text type="success" strong style={{ fontSize: 18 }}>
                    Payment Successful! 🎉
                </Text>
            );
        } else if (orderStatus === 'processing') {
            statusMsg = (
                <Text type="warning" strong style={{ fontSize: 18 }}>
                    Your payment is being processed...
                </Text>
            );
        } else if (orderStatus === 'cancelled' || orderStatus === 'failed') {
            statusMsg = (
                <Text type="danger" strong style={{ fontSize: 18 }}>
                    Payment failed or was cancelled. Please try again or contact support.
                </Text>
            );
        } else {
            statusMsg = (
                <Text strong style={{ fontSize: 18 }}>
                    Payment status unknown. Please contact support if you have questions.
                </Text>
            );
        }

        return (
            <Layout style={{ minHeight: "100vh", backgroundColor: "#fff" }}>
                <Content style={{ display: "flex", justifyContent: "center", alignItems: "center" }}>
                    <div style={{ width: 800, padding: 24 }}>
                        <Row justify="center" style={{ marginBottom: 15 }}>
                            <Title level={2}>{t("thankYou")}</Title>
                        </Row>
                        <Row justify="center" style={{ marginBottom: 15 }}>
                            <Text type="secondary" strong>
                                {t("orderNumber")}: #{orderData.id}
                            </Text>
                        </Row>
                        <Row justify="center" style={{ marginBottom: 15 }}>
                            <p>{t("appreciatePurchase")}</p>
                            <p>{t("orderPlacedOnDate", { date: formattedDate })}</p>
                        </Row>
                        <Row justify="center" style={{ marginBottom: 15 }}>
                            {statusMsg}
                        </Row>
                        <Table
                            columns={columns}
                            dataSource={products}
                            pagination={false}
                            bordered
                            style={{ marginBottom: 32 }}
                            summary={() => (
                                <Table.Summary.Row>
                                    <Table.Summary.Cell colSpan={2} align="right">
                                        <Text strong>{t('orderTotal')}:</Text>
                                    </Table.Summary.Cell>
                                    <Table.Summary.Cell align="right">
                                        <Text strong>€{total}</Text>
                                    </Table.Summary.Cell>
                                </Table.Summary.Row>
                            )}
                        />
                        <Row justify="center" style={{ width: '100%' }}>
                            <Button
                                type="primary"
                                size="large"
                                icon={<ArrowLeftOutlined />}
                                onClick={goToProducts}
                            >
                                {t("backToHome")}
                            </Button>
                        </Row>
                    </div>
                </Content>
            </Layout>
        );
    }
}
