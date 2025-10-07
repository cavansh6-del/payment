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


    const completeOrder = async () => {
        setLoadingBtn(true);
        try {
            const response = await createPayment(orderData,userComment);

            console.log("response completeOrder",response);
            if (response.data.url) {
                window.location.href = response.data.url; // ریدایرکت سمت کلاینت
            }
            setLoadingBtn(false);
            /*if (response.success) {
                navigate('/thank-you', {
                    state: {
                        items: selectedItems,
                        billingAddress,
                        shippingAddress,
                        countries,
                        shippingInfo,
                        externalOrderNumber: response.data.externalOrderNumber
                    }
                });
            } else {
                messageApi.open({
                    type: 'error',
                    content: response.error.join('\n'),
                });
            }*/
        } catch (error) {
            console.error("Order failed:", error);
        }
    };

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
                {contextHolder}
                <Header style={{ background: '#fff', padding: 24 }}>
                    <Col xs={0} md={6}>
                        <Logo />
                    </Col>
                </Header>
                <Content style={{ display: 'flex', justifyContent: 'center', alignItems: 'center' }}>
                    <div style={{ width: 800, padding: 24 }}>
                        <Row justify="center" style={{ marginBottom: 32 }}>
                            <Title level={2}>{t('confirmOrder')}</Title>
                        </Row>

                        <Row gutter={24} style={{ marginBottom: 24 }}>
                            <Col span={24}>
                                <Card title={t('userInfo')} variant="borderless">
                                    <span style={{ fontSize: '16px', fontWeight: 'bold' }}></span> <b>{user?.email}</b>
                                </Card>
                            </Col>
                        </Row>

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
                                            {/* <Text strong>€{total.toFixed(2)}</Text>*/}
                                            <Text strong>€{total}</Text>
                                        </Table.Summary.Cell>
                                    </Table.Summary.Row>,
                                );

                                return <>{rows}</>;
                            }}
                            style={{ marginBottom: 32 }}
                        />
                        <div style={{ marginBottom: 24 }}>
                            <Text strong>{t('optionalComment') || 'Kommentar (optional)'}:</Text>
                            <Input.TextArea
                                value={userComment}
                                onChange={(e) => setUserComment(e.target.value)}
                                placeholder={t('writeYourComment') || 'Ihre Anmerkung zur Bestellung...'}
                                autoSize={{ minRows: 4, maxRows: 5 }}
                            />
                        </div>
                        <Row justify="space-between" style={{ width: '100%' }}>
                            <Col>
                                <Button type="default" size="large" icon={<ArrowLeftOutlined />}
                                        onClick={() => navigate(-1)}>
                                    {t('back')}
                                </Button>
                            </Col>
                            <Col>
                                <Button type="primary" size="large" onClick={completeOrder} loading={loadingBtn}>
                                    {t('completeOrder')}
                                </Button>
                            </Col>
                        </Row>
                    </div>
                </Content>
            </Layout>
        );
    }
}
