import React from 'react';
import {InputNumber, Space, Table, Tag, Tooltip, Typography} from 'antd';
import {useTranslation} from 'react-i18next';
import {price} from "@/util/price.js";

const ProductTable = ({
                          loading,
                          tableKey,
                          products,
                          sortInfo,
                          handleTableChange,
                          selectedItems,
                          handleQuantityChange
                      }) => {
    const {t, i18n} = useTranslation();

    const columns = [
        {
            title: t('sku'),
            dataIndex: 'number',
            key: 'number',
            sorter: true,
            sortDirections: ['ascend', 'descend'],
            sortOrder: sortInfo.field === 'sku' ? sortInfo.order : null
        },
        {
            title: t('ean'),
            dataIndex: 'ean',
            key: 'ean',
            sorter: true,
            sortDirections: ['ascend', 'descend'],
            sortOrder: sortInfo.field === 'ean' ? sortInfo.order : null
        },
        {
            title: t('description'),
            dataIndex: 'name',
            key: 'description',
            width: 250,
            render: (text, record) => {
                const lang = i18n.language || 'en';
                const translatedName = record.translate?.[lang]?.name;
                return (
                    <Tooltip title={translatedName || text}>
                        {translatedName || text}
                    </Tooltip>
                );
            }

        },
        {title: t('brand'), dataIndex: 'brand', key: 'brand'},
        {
            title: t('category'),
            dataIndex: 'category',
            key: 'category',
            sorter: true,
            sortDirections: ['ascend', 'descend'],
            sortOrder: sortInfo.field === 'category' ? sortInfo.order : null
        },
        {
            title: t('price'),
            key: 'price',
            render: (text, record) => {
                const prices = record.prices || [];

                if (prices.length === 1 && prices[0].amount === 1) {
                    return <Typography.Text>
                        {price(prices[0].price)}
                    </Typography.Text>;
                }

                return (
                    <Space direction="horizontal" size={16}>
                        {prices.map((p, index) => {
                            const amountLabel = index === prices.length - 1
                                ? `${p.amount}+`
                                : `${p.amount}-${prices[index + 1]?.amount - 1 || p.amount}`;

                            return (
                                <Space key={index} direction="vertical" size={0} style={{textAlign: 'center'}}>
                                    <Typography.Text strong>{amountLabel}</Typography.Text>
                                    <Typography.Text>
                                        {price(p.price)}
                                        <small style={{fontSize: '9px', color: '#999'}}> {t('net')}</small>
                                    </Typography.Text>
                                </Space>
                            );
                        })}
                    </Space>
                );
            }
        },
        {
            title: t('qty'),
            render: (text, record) => {
                let tag = '';
                let color = '';

                if (record.is_disabled === true) {
                    tag = t('notAvailable');
                    color = 'red';
                } else if (record.stock_count > 10) {
                    tag = t('available');
                    color = 'green';
                } else if (record.stock_count > 0) {
                    tag = t('limitedStock');
                    color = 'gold';
                } else {
                    tag = t('notAvailable');
                    color = 'red';
                }

                return (
                    <div style={{display: 'flex', flexDirection: 'column', alignItems: 'start', gap: 4}}>
                        <InputNumber
                            min={0}
                            defaultValue={0}
                            value={selectedItems[record.id]?.quantity || 0}
                            onChange={(value) => handleQuantityChange(record, value)}
                        />
                        <Tag color={color} key={tag} style={{fontSize: '10px'}}>
                            {tag}
                        </Tag>
                    </div>
                );
            }
        }
    ];

    return (
        <>
            <Table
                key={tableKey}
                className="mobile-sticky-header"
                rowKey="number"
                dataSource={products.products}
                onChange={handleTableChange}
                columns={columns}
                pagination={{
                    current: products.meta.current_page,
                    pageSize: products.meta.per_page,
                    total: products.meta.total,
                    showSizeChanger: false,
                }}
                loading={loading}
                scroll={{x: 'max-content'}}
                tableLayout="fixed"
                locale={{
                    emptyText: t('noData'),
                }}
            />
        </>
    );
};

export default ProductTable;
