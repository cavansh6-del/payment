import React, { useEffect, useState  } from 'react';
import {Card, Row, Col, Typography, Button, Layout, Form, Input, message, Spin} from "antd";
import {LoadingOutlined, MailOutlined} from '@ant-design/icons';
import {checkLogin} from '../services/api';
import { useNavigate } from 'react-router-dom';

const { Title } = Typography;
const { Header, Content } = Layout;
const antIcon =   <LoadingOutlined style={{fontSize: 24, color: '#7230ff',}} spin/>;
export default function B2bLogin() {
    const [loading, setLoading] = useState(false);
    const [pageLoading, setPageLoading] = useState(true);
    const [form] = Form.useForm();
    const [msgApi, contextHolder] = message.useMessage();
    const navigate = useNavigate();

    useEffect(() => {
        // simulate async checkLogin
        const doCheck = async () => {
            try {
                await checkLogin(navigate);
            } finally {
                setPageLoading(false);
            }
        };
        doCheck();
    }, []);




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
            <Layout style={{ minHeight: "100vh", backgroundColor: "#fff" }}>
                {contextHolder}
                <Content style={{ display: "flex", justifyContent: "center", alignItems: "center" }}>
                    <div style={{ width: 400, padding: 24 }}>
                        <Row justify="center" style={{ marginBottom: 32 }}>
                            <Title level={3}>2E B2B Login</Title>
                        </Row>

                        <Card>
                            <Form form={form} layout="vertical" onFinish={handleSendLink}>
                                <Form.Item
                                    label="Business Email"
                                    name="email"
                                    rules={[
                                        { required: true, message: "Please enter your email address" },
                                        { type: 'email', message: "Invalid email format" }
                                    ]}
                                >
                                    <Input
                                        placeholder="email@example.com"
                                        prefix={<MailOutlined />}
                                        size="large"
                                    />
                                </Form.Item>

                                <Form.Item>
                                    <Button type="primary" htmlType="submit" block loading={loading}>
                                        Send Login Link
                                    </Button>
                                </Form.Item>
                            </Form>
                        </Card>
                    </div>
                </Content>
            </Layout>
        );
    }
}
