import React from 'react';
import { Input  } from 'antd';
const FloatingLabelInput = ({ label, value, onChange, id, autoComplete,readOnly =false }) => {
    return (
        <div className="floating-label-input">
            <Input
                id={id}
                type="text"
                value={value}
                onChange={onChange}
                placeholder=" "
                autoComplete={autoComplete}
                readOnly={readOnly}
            />
            <label htmlFor={id}>{label}</label>
        </div>
    );
};

export default FloatingLabelInput;
