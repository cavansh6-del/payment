import React, {useEffect, useState} from 'react';
import {Table, Input, Select, Row, Col, Spin, Tooltip, Typography, Space, InputNumber, Tag} from 'antd';
import { useTranslation } from 'react-i18next';

const ProductFilter = ({ search, setSearch, setBrand, setCategory }) => {
    const { t } = useTranslation();

    const [categories, setCategories] = useState([]);
    const [category, setLocalCategory] = useState(null);
    const [brands, setBrands] = useState([]);


    useEffect(() => {

    }, []);


    const handleCategoryChange = (value) => {
        setLocalCategory(value);
        setCategory(value);
    };

    return (
        <Row gutter={[16, 16]} style={{ marginBottom: 16 }}>
            <Col xs={24} sm={12} md={8}>
                <Input
                    placeholder={t('searchProduct')}
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                />
            </Col>
            <Col xs={24} sm={12} md={8}>
                <Select
                    placeholder={t('filterBrand')}
                    style={{ width: '100%' }}
                    allowClear
                    onChange={(value) => setBrand(value)}
                >
                    {brands.map((brand) => (
                        <Select.Option key={brand} value={brand}>
                            {brand}
                        </Select.Option>
                    ))}
                </Select>
            </Col>
            <Col xs={24} sm={12} md={8}>
                <Select
                    placeholder={t('filterCategory')}
                    style={{ width: '100%' }}
                    allowClear
                    onChange={(value) => handleCategoryChange(value)}
                    value={category}
                >
                    {categories.map((cat) => (
                        <Select.Option key={cat} value={cat}>
                            {cat}
                        </Select.Option>
                    ))}
                </Select>
            </Col>
        </Row>
    );
};

export default ProductFilter;
