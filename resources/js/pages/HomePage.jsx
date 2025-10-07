import React, {useEffect, useRef, useState} from 'react';
import {Button, Col, Layout, message, Row, Select, Spin, Typography} from 'antd';
import { getProducts} from '../services/api';
import {useNavigate} from 'react-router-dom';
import FloatingLabelInput from '../components/FloatingLabelInput.jsx'
import Container from '../components/Container.jsx';
import {LoadingOutlined} from '@ant-design/icons';
import {useTranslation} from 'react-i18next';
import Logo from "@/components/Logo.jsx";
import LogoutButton from "@/components/LogoutButton.jsx";
import ProductTable from '@/components/ProductTable.jsx';
import ProductFilter from '@/components/ProductFilter.jsx';

const { Title, Text } = Typography;
const { Content } = Layout;

const antIcon =   <LoadingOutlined style={{fontSize: 24, color: '#7230ff',}} spin/>;
function HomePage({ onAddToOrder }) {
    const [messageApi, contextHolder] = message.useMessage();
    const { t, i18n } = useTranslation();
    const [loading, setLoading] = useState(false);
    const [pageLoading, setPageLoading] = useState(true);

    const [products, setProducts] = useState([]);
    const [selectedItems, setSelectedItems] = useState({});
    const [subtotal, setSubtotal] = useState(0);


    const [countries, setCountries] = useState([]);
    const [search, setSearch] = useState('');
    const [brand, setBrand] = useState(null);
    const [category, setCategory] = useState(null);
    const [defaultCategory, setDefaultCategory] = useState(null);


    const [currentLang, setCurrentLang] = useState([]);


    const [billingAddress, setBillingAddress] = useState({});
    const [shippingAddress, setShippingAddress] = useState({});
    const shippingAddressRef = useRef(shippingAddress);
    const [tableKey, setTableKey] = useState(0);

    const [sortInfo, setSortInfo] = useState({
        order: null,
        field: null
    });


    const navigate = useNavigate();

    useEffect(() => {
        const storedData = sessionStorage.getItem("orderData");

        if (storedData) {
            try {
                const parsed = JSON.parse(storedData);
                setSelectedItems(parsed.selectedItems);
                setShippingAddress(parsed.shippingAddress);
                shippingAddressRef.current = parsed.shippingAddress;
                const lang = fetchCountryLanguage(parsed.shippingAddress.country);
                setCurrentLang(lang);
                i18n.changeLanguage(lang);
            } catch (e) {
                console.error("Error parsing orderData", e);
                sessionStorage.removeItem("orderData");
            }
        }
    }, [navigate]);

    useEffect(() => {

        const hasFilters =
            (search && search.trim() !== '') ||
            category !== null ||
            brand !== null;

        if (!hasFilters) return;

        const handler = setTimeout(() => {
            fetchProducts();
        }, 500);
        return () => {
            clearTimeout(handler);
        };
    }, [search, category, brand]);

    const fetchCountryName = (code) => {
        const country = countries.find(c => c.code === code);
        return country ? (currentLang === 'en' ? country.en : country.de) : code;
    }

    const fetchCountryLanguage = (code) => {
        return (countries.find(c => c.code === code)?.language || 'en').toLowerCase();
    }

    // Use useEffect to calculate subtotal after selectedItems changes
    useEffect(() => {
        setSubtotal(Object.values(selectedItems).reduce((acc, item) => acc + item.total, 0));
    }, [selectedItems]);

    const fetchProducts = async (page = 1,sortBy = 'id', sortOrder = 'desc',defaultCat = null) => {
        setLoading(true);
        const response = await getProducts({page, sortBy, sortOrder, search, category : category ? category : defaultCat, brand }, navigate);
        setProducts(response?.data || []);
        setLoading(false);
    };




    const handleTableChange = (pagination, filters, sorter) => {
        const page = pagination.current;
        const sortField = sorter.field;
        const sortOrder = sorter.order === 'ascend' ? 'asc' : 'desc';

        setSortInfo({
            order: sorter.order,
            field: sorter.field
        });

        fetchProducts( page, sortField, sortOrder );
    };
    const handleQuantityChange = (product, quantity) => {

        if (quantity === 0) {
            setSelectedItems(prev => {
                const updated = { ...prev };
                delete updated[product.id];
                return updated;
            });
            return;
        }

        const validPrices = product.prices.filter(p => quantity >= p.amount);
        const pricing = validPrices.length > 0 ? validPrices.sort((a, b) => a.price - b.price)[0] : null;
        const finalPrice = pricing ? pricing.price : 0;

        const lang = i18n.language || 'en';
        const translatedName = product.translate?.[lang]?.name;

        setSelectedItems(prev => ({
            ...prev,
            [product.id]: {
                quantity,
                description: translatedName || product.name,
                price: finalPrice,
                total: finalPrice * quantity,
            },
        }));
    };

    const handleConfirm = () => {
        // Check if any product is selected
        if (Object.keys(selectedItems).length === 0) {
            messageApi.open({
                type: 'error',
                content: t("error.selectProduct"),
            });
            return;
        }

        // Check if both addresses are provided
        if (!isAddressValid(billingAddress) || !isAddressValid(shippingAddress)) {
            messageApi.open({
                type: 'error',
                content: t("error.provideShippingAddresses"),
            });
            return;
        }

        // Navigate to ConfirmOrderPage
        const orderData = {
            selectedItems,
            billingAddress,
            shippingAddress,
        };
        sessionStorage.setItem("orderData", JSON.stringify(orderData));
        navigate("/confirm-order", {
            state: { status: true }
        });

    };

    const isAddressValid = (address) => {
        return address && address.name && address.street && address.city && address.zipCode && address.country;
    };

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
            <Container>
                {contextHolder}
                <Layout style={{ minHeight: '100vh', backgroundColor: '#fff' }}>
                    <Content style={{ padding: '32px 16px', margin: '0 auto', width: '100%' }}>
                        <Row gutter={[16, 16]} style={{ marginBottom: 24 }}>
                            <Col xs={0} md={6}>
                                <Logo />
                            </Col>
                            <Col xs={24} sm={12} md={9}>
                                <Title level={4} style={{ marginTop: 0 }}>{t('billing')}</Title>
                                <FloatingLabelInput label={t('fullName')} readOnly value={billingAddress.name}/>
                                <FloatingLabelInput label={t('street')} readOnly value={billingAddress.street}/>
                                <FloatingLabelInput label={t('city')} readOnly value={billingAddress.city}/>
                                <FloatingLabelInput label={t('zip')} readOnly value={billingAddress.zipCode}/>
                                <FloatingLabelInput label={t('country')} readOnly value={fetchCountryName(billingAddress.country)}/>
                            </Col>
                            <Col xs={24} sm={12} md={9}>
                                <Title level={4} style={{marginTop: 0}}>{t('shipping')}</Title>
                                <FloatingLabelInput
                                    label={t('fullName')}
                                    value={shippingAddress.name}
                                    onChange={e =>
                                        setShippingAddress(prev => ({ ...prev, name: e.target.value }))
                                    }
                                    id="shipping-name"
                                    autoComplete="name"
                                />

                                <FloatingLabelInput
                                    label={t('street')}
                                    value={shippingAddress.street}
                                    onChange={e =>
                                        setShippingAddress(prev => ({ ...prev, street: e.target.value }))
                                    }
                                    id="shipping-street"
                                    autoComplete="address-line1"
                                />

                                <FloatingLabelInput
                                    label={t('city')}
                                    value={shippingAddress.city}
                                    onChange={e =>
                                        setShippingAddress(prev => ({ ...prev, city: e.target.value }))
                                    }
                                    id="shipping-city"
                                    autoComplete="address-level2"
                                />

                                <FloatingLabelInput
                                    label={t('zip')}
                                    value={shippingAddress.zipCode}
                                    onChange={e =>
                                        setShippingAddress(prev => ({ ...prev, zipCode: e.target.value }))
                                    }
                                    id="shipping-zip"
                                    autoComplete="postal-code"
                                />

                                <Select
                                    showSearch
                                    value={shippingAddress.country}
                                    onChange={(value) => {
                                        setShippingAddress(prev => ({...prev, country: value}))
                                        const lang = fetchCountryLanguage(value);
                                        setCurrentLang(lang);
                                        i18n.changeLanguage(lang);
                                        setTableKey(prev => prev + 1);
                                    }}
                                    style={{ width: '100%' }}
                                    optionFilterProp="children"
                                    filterOption={(input, option) =>
                                        option.children.toLowerCase().includes(input.toLowerCase())
                                    }
                                >
                                    <Select.Option value="">{t('selectCountry')}</Select.Option>
                                    {countries.map(country => (
                                        <Select.Option key={country.code} value={country.code}>
                                            {currentLang === 'de' ? country.de : country.en}
                                        </Select.Option>
                                    ))}
                                </Select>
                            </Col>
                        </Row>

                        <ProductFilter
                            search={search}
                            setSearch={setSearch}
                            setBrand={setBrand}
                            setCategory={setCategory}
                        />

                        {loading ? (
                            <Row justify="center" align="middle" style={{ height: '100vh' }}>
                                <Col>
                                    <Spin size="large"/>
                                </Col>
                            </Row>
                        ) : (
                            <ProductTable
                                loading={loading}
                                tableKey={tableKey}
                                products={products}
                                sortInfo={sortInfo}
                                handleTableChange={handleTableChange}
                                selectedItems={selectedItems}
                                handleQuantityChange={handleQuantityChange}
                            />
                        )}

                        <Row justify="space-between" align="bottom" style={{ marginTop: 24,flexDirection: 'row-reverse' }}>
                            <Col sm={14} md={10} style={{width: '100%'}}>
                                <Row justify="space-between" align="middle">
                                    <Col>
                                        <Title level={3} style={{ marginBottom: 0 }}>{t('subtotal')}:</Title>
                                    </Col>
                                    <Col>
                                        <Title level={3} style={{ marginBottom: 0 }}>€{subtotal.toFixed(2)}</Title>
                                    </Col>
                                </Row>
                                <div style={{ marginTop: 16, color: '#595959' }}>
                                    <Text style={{ display: 'block', marginBottom: 4 }}>{t('paymentNote1')}</Text>
                                    <Text style={{ display: 'block' }}>{t('paymentNote2')}</Text>
                                </div>
                                <div style={{ marginTop: 24, textAlign: 'center' }}>
                                    <Button type="primary" block onClick={handleConfirm}>
                                        {t('confirmOrder')}
                                    </Button>
                                </div>
                            </Col>
                            <Col xs={24} sm={3} style={{  marginTop: 32,textAlign: 'left'}}>
                                <LogoutButton />
                            </Col>
                        </Row>
                    </Content>
                </Layout>
            </Container>
        );
    }
}


export default HomePage;
