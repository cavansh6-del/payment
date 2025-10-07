import React, { useEffect, useState } from 'react';
import {
    Card,
    Row,
    Typography,
    Button,
    Layout,
    Form,
    Input,
    message,
    Spin,
    Modal,
    Select,
    Checkbox,
    Segmented,
    Radio,
    ConfigProvider,
    theme,
} from "antd";
import { LoadingOutlined, MailOutlined, LockOutlined } from '@ant-design/icons';
import {
    checkLogin,
    login,
    register,
    checkEmailExists,
    getProducts,
    submitOrder,
    createPayment
} from '../services/api';
import { useNavigate } from 'react-router-dom';

const { Title } = Typography;
const { Header, Content } = Layout;
const antIcon = <LoadingOutlined style={{ fontSize: 24, color: '#7230ff', }} spin />;

const subscriptionOptions = [
    { label: "Basic", value: "basic", price: "10 € / month" },
    { label: "Pro", value: "pro", price: "25 € / month" },
    { label: "Enterprise", value: "enterprise", price: "55 € / month" },
];

export default function SelectSubscription() {
    const [isModalLoading, setIsModalLoading] = useState(false);
    const [loading, setLoading] = useState(false);
    const [pageLoading, setPageLoading] = useState(true);
    const [form] = Form.useForm();
    const [modalForm] = Form.useForm();
    const [msgApi, contextHolder] = message.useMessage();
    const navigate = useNavigate();
    const [selectedPlan, setSelectedPlan] = useState(null);
    const [modalOpen, setModalOpen] = useState(false);
    const [isExisting, setIsExisting] = useState(false); // ایمیل قبلاً وجود داشته؟
    const [userEmail, setUserEmail] = useState("");
    const [data, setData] = useState("");


    const [products, setProducts] = useState([]);
    const [paymentMethod, setPaymentMethod] = useState("paypal");



    useEffect(() => {


        setPageLoading(false);
        // simulate async checkLogin
        const doCheck = async () => {
            try {
                const product = await getProducts();
                console.log('product', product);
                setProducts(product);
            } finally {
                setPageLoading(false);
            }
        };
        doCheck();
    }, []);

    const handleSendLink = async (values) => {
        /*
                setLoading(true);
                setUserEmail(values.email);
                const orders = await submitOrder(values);

                const response = await createPayment(orders.data.order, "");

                console.log("response completeOrder", response);
                if (response.data.url) {
                    window.location.href = response.data.url; // ریدایرکت سمت کلاینت
                }


        */

        setLoading(true);
        setUserEmail(values.email);
        setData(values);
        const exists = await checkEmailExists(values.email,navigate);
        console.log('exists',exists);
        setIsExisting(exists);
        setModalOpen(true);
        setLoading(false);

    };


        const handleModalSubmit = async () => {
            setIsModalLoading(true);
            const modalValues = await modalForm.validateFields();
            let result = false;
            if (isExisting) {
                result = await login(userEmail,modalValues.password);
                console.log('result');
            } else {
                result = await register(userEmail,modalValues.password);
                if(result){
                    message.success("Account created!");
                }
            }
           if(result){
               console.log("selectedOption",data)
               message.success("Logged in successfully!");
               const orders = await submitOrder(data);
               console.log('orders',orders);

               if(orders.data.url){
                   window.location = orders.data.url;
                   return;
               }
               navigate('/confirm-order', { state: orders.data.order });
               return;
           }else{
               message.error("login problem!");
               //setModalOpen(false);
               modalForm.resetFields();
           }

            setIsModalLoading(false);

        };


    const handlePlanChange = (e) => setSelectedPlan(e.target.value);
    const selectedOption = subscriptionOptions.find(opt => opt.id === selectedPlan);
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
            <ConfigProvider
                theme={{
                    algorithm: theme.defaultAlgorithm,
                    token: {
                        borderRadiusLG: 24,
                        colorPrimary: "#1677ff",
                        controlHeightLG: 44,
                        fontSizeHeading2: 28,
                    },
                }}
            >
                {contextHolder}
                <Layout
                    style={{
                        minHeight: "100vh",
                        background:
                            "linear-gradient(180deg, rgba(246,248,252,1) 0%, rgba(255,255,255,1) 100%)",
                    }}
                >
                    <Content
                        style={{
                            display: "flex",
                            justifyContent: "center",
                            alignItems: "center",
                            padding: "48px 16px",
                        }}
                    >
                        <Card
                            style={{
                                width: "100%",
                                maxWidth: 640,
                                padding: 24,
                                boxShadow:
                                    "0 20px 45px rgba(17,24,39,0.06), 0 8px 24px rgba(17,24,39,0.04)",
                            }}
                            bodyStyle={{ padding: 24 }}
                        >
                            <Row justify="center" style={{ marginBottom: 12 }}>
                                <Title level={2} style={{ margin: 0 }}>
                                    Choose your service
                                </Title>
                            </Row>

                            <Form
                                form={form}
                                layout="vertical"
                                onFinish={handleSendLink}
                                requiredMark="optional"
                                style={{ marginTop: 16 }}
                                initialValues={{
                                    paymentMethod: "paypal", // مقدار پیش‌فرض از اینجا میاد
                                }}
                            >
                                {/* Choose your service (Select) */}
                                <Form.Item
                                    label="Choose your service"
                                    name="subscription"
                                    rules={[{ required: true, message: "Please choose a service" }]}
                                >
                                    <Select
                                        size="large"
                                        placeholder="Select a subscription"
                                        onChange={(val) => setSelectedPlan(val)}
                                        options={products.map((p) => ({
                                            value: p.id,
                                            label: (
                                                <div
                                                    style={{
                                                        display: "flex",
                                                        alignItems: "center",
                                                        justifyContent: "space-between",
                                                        width: "100%",
                                                    }}
                                                >
                                                    <span style={{ fontWeight: 500 }}>{p.name}</span>
                                                    {p.price ? (
                                                        <Typography.Text type="secondary">{p.price} $ / month</Typography.Text>
                                                    ) : null}
                                                </div>
                                            ),
                                        }))}
                                    />
                                </Form.Item>

                                {/* IPTV Subscription (create or renew) */}
                                {/*                                <Form.Item label="IPTV Subscription" name="iptvMode">
                                    <Segmented
                                        size="large"
                                        block
                                        options={[
                                            { label: "create new account", value: "create" },
                                            { label: "renew existing", value: "renew" },
                                        ]}
                                        defaultValue="create"
                                    />
                                </Form.Item>*/}

                                {/* Email */}
                                <Form.Item
                                    label="Your Email Address"
                                    name="email"
                                    rules={[
                                        { required: true, message: "Please enter your email address" },
                                        { type: "email", message: "Invalid email format" },
                                    ]}
                                >
                                    <Input
                                        size="large"
                                        placeholder="email@example.com"
                                        prefix={<MailOutlined />}
                                    />
                                </Form.Item>

                                {/* Terms */}
                                <Form.Item
                                    name="agree"
                                    valuePropName="checked"
                                    rules={[
                                        {
                                            validator: (_, v) =>
                                                v
                                                    ? Promise.resolve()
                                                    : Promise.reject(new Error("Please agree to terms")),
                                        },
                                    ]}
                                >
                                    <Checkbox>I Agree to Terms</Checkbox>
                                </Form.Item>

                                <Typography.Paragraph style={{ marginTop: -8, color: "#6b7280" }}>
                                    <Typography.Text>
                                        <b>Note:</b> We will fully refund purchases made within 7
                                        days. Other refunds may be partial based on used subscription
                                        period. Our support team will contact you in all refund cases.
                                    </Typography.Text>
                                </Typography.Paragraph>

                                {/* Payment Method */}
                                <Form.Item
                                    label={<span style={{ fontWeight: 600 }}>Payment Method</span>}
                                    name="paymentMethod"
                                    initialValues={{ paymentMethod: "paypal" }}
                                >
                                    <div
                                        style={{
                                            border: "1px solid #E5E7EB", // #e5e7eb = gray-200
                                            borderRadius: 9999,          // کپسولی
                                            padding: 8,
                                        }}
                                    >
                                        <Radio.Group
                                            onChange={(e) => setPaymentMethod(e.target.value)}
                                            style={{
                                                display: "flex",
                                                alignItems: "center",
                                                justifyContent: "space-around",
                                                width: "100%",
                                                padding: "4px 8px",
                                            }}
                                            value={paymentMethod}
                                            defaultValue="paypal"
                                        >
                                            <Radio value="paypal" style={{ margin: 0 }}>
                                                <span style={{ display: "inline-flex", alignItems: "center", gap: 8 }}>
                                                    <span style={{ fontWeight: 700 }}>
                                                        <span style={{ color: "#005EA6" }}>Pay</span>
                                                        <span style={{ color: "#0070E0" }}>Pal</span>
                                                    </span>
                                                </span>
                                            </Radio>

                                            <Radio value="mercuryo" style={{ margin: 0 }}>
                                                <span style={{ fontWeight: 500 }}>Credit/Debit Card</span>
                                            </Radio>

                                            <Radio value="paygate" style={{ margin: 0 }}>
                                                <span style={{ fontWeight: 500 }}>Crypto</span>
                                            </Radio>
                                        </Radio.Group>
                                    </div>
                                </Form.Item>

                                <Typography.Paragraph style={{ color: "#6b7280", marginTop: -8 }}>
                                    your account will be created immediately after payment, and you
                                    can manage your account and edit the channels.
                                </Typography.Paragraph>

                                {/* CTA */}
                                <Form.Item style={{ marginTop: 16 }}>
                                    <Button
                                        type="primary"
                                        htmlType="submit"
                                        size="large"
                                        block
                                        shape="round"
                                        loading={loading}
                                    >
                                        continue to payment
                                    </Button>
                                </Form.Item>
                            </Form>
                        </Card>
                    </Content>
                </Layout>

                {/* Login / Register Modal */}
                <Modal
                    open={modalOpen}
                    title={isExisting ? "Enter your password" : "Set a password"}
                    onCancel={() => setModalOpen(false)}
                    onOk={handleModalSubmit}
                    okText={isExisting ? "Login" : "Create Account"}
                    confirmLoading={isModalLoading}
                >
                    <Form form={modalForm} layout="vertical" preserve={false}>
                        <Form.Item>
                            <b>{userEmail}</b>
                        </Form.Item>
                        <Form.Item
                            label={isExisting ? "Password" : "Choose Password"}
                            name="password"
                            rules={[
                                { required: true, message: "Please enter your password" },
                                { min: 6, message: "Password must be at least 6 characters" },
                            ]}
                        >
                            <Input.Password
                                prefix={<LockOutlined />}
                                placeholder={isExisting ? "Enter password" : "Set your password"}
                            />
                        </Form.Item>
                        {!isExisting && (
                            <Form.Item
                                label="Confirm Password"
                                name="confirm"
                                dependencies={["password"]}
                                rules={[
                                    { required: true, message: "Please confirm your password" },
                                    ({ getFieldValue }) => ({
                                        validator(_, value) {
                                            if (!value || getFieldValue("password") === value) {
                                                return Promise.resolve();
                                            }
                                            return Promise.reject(new Error("Passwords do not match!"));
                                        },
                                    }),
                                ]}
                            >
                                <Input.Password prefix={<LockOutlined />} placeholder="Confirm password" />
                            </Form.Item>
                        )}
                    </Form>
                </Modal>
            </ConfigProvider>
        );
    }
}
