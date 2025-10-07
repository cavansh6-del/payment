import React, { useEffect, useState, useMemo } from 'react';
import { Card, Row, Col, Table, Typography, Button, Layout, message, Input, Spin } from 'antd';
import { useLocation, useNavigate } from "react-router-dom";
import { useTranslation } from 'react-i18next';
import { createPayment, getProducts, submitOrder } from '../services/api';
import { ArrowLeftOutlined, LoadingOutlined } from '@ant-design/icons';
import Logo from "@/components/Logo.jsx";
const { Title, Text } = Typography;
const { Header, Content } = Layout;

const antIcon =   <LoadingOutlined style={{fontSize: 24, color: '#7230ff',}} spin/>;

export default function ConfirmOrder() {
    const location = useLocation();
    const [messageApi, contextHolder] = message.useMessage();
    const { t, i18n } = useTranslation();
    const navigate = useNavigate();
    const [pageLoading, setPageLoading] = useState(true);
    const [orderData, setOrderData] = useState(location.state || {});
    const [userComment, setUserComment] = useState("");
    const [user, setUser] = useState(null);
    const [products, setProducts] = useState(null);
    const [total, setTotal] = useState(0);


    const [loadingBtn, setLoadingBtn] = useState(false);



    useEffect(() => {

        const token = localStorage.getItem("token");

        if (!token) {
            navigate('/');
            return;
        }
        setUser(JSON.parse(localStorage.getItem("data")));

        setTotal(orderData.total_amount);
        setProducts([orderData]);
        setPageLoading(false);
    }, [navigate]);

    useEffect(() => {
        // i18n.changeLanguage(fetchCountryLanguage(code));
    }, [orderData, i18n]);



    const currentLang = 'en';


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
        return (
            <Layout style={{ minHeight: '100vh', backgroundColor: '#fff' }}>
                <Header style={{ background: '#fff', padding: 24 }}>
                    <Col xs={0} md={6}>
                        <Logo />
                    </Col>
                </Header>
                <Content style={{ display: 'flex', justifyContent: 'center', alignItems: 'center' }}>
                    <div style={{ width: 800, padding: 24 }}>
                        <Row justify="center" style={{ marginBottom: 32 }}>
                            <Title level={2}>{t('Thank You for Your Order')}</Title>
                        </Row>

                        <Row gutter={24} style={{ marginBottom: 24 }}>
                            <Col span={24}>
                                <Card  variant="borderless">
                                    <span>{t('Email address')} : </span>
                                    <b>{user?.email}</b>
                                </Card>
                            </Col>
                        </Row>

                        {/* پیام ارسال ایمیل */}
                        <Row justify="left" style={{ marginBottom: 15 ,paddingTop: 20}}>
                            <Text  style={{ fontSize: 16 }}>
                                Thank you for your order! Your order has been successfully placed, and a payment link has been sent to your email address. Please check your inbox for the email.
                            </Text>
                        </Row>
                        <Row justify="left" style={{ marginBottom: 15 }}>
                            <Text  style={{ fontSize: 16 }}>
                                If you do not see the email in your inbox, kindly check your Spam or Junk folder as well.
                            </Text>
                        </Row>
                        <Row justify="left" style={{ marginBottom: 15,paddingBottom:40 }}>
                            <Text  style={{ fontSize: 16 }}>
                                If you still haven't received the email, please contact our support team.
                            </Text>
                        </Row>

{/*
                        <Table
                            columns={columns}
                            dataSource={products}
                            pagination={false}
                            rowKey="id"
                            bordered
                            summary={() => {
                                const rows = [];
                                rows.push(
                                    <Table.Summary.Row key="subtotal">
                                        <Table.Summary.Cell index={0} colSpan={2} align="right">
                                            <Text strong>{t('orderTotal')}:</Text>
                                        </Table.Summary.Cell>
                                        <Table.Summary.Cell index={2} align="right">
                                            <Text strong>€{total}</Text>
                                        </Table.Summary.Cell>
                                    </Table.Summary.Row>,
                                );
                                return <>{rows}</>;
                            }}
                            style={{ marginBottom: 32 }}
                        />
*/}

                        <Row justify="space-between" style={{ width: '100%' }}>
                            <Col>
                                <Button type="default" size="large" icon={<ArrowLeftOutlined />} onClick={() => navigate(-1)}>
                                    {t('back')}
                                </Button>
                            </Col>
                        </Row>
                    </div>
                </Content>
            </Layout>
        );
    }
}
