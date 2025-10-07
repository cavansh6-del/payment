import axios from 'axios';


const API_URL =  import.meta.env.VITE_API_URL;
const getAuthToken = () => {
    return localStorage.getItem('token');
};

axios.interceptors.request.use(
    (config) => {
        const token = getAuthToken();
        if (token) {
            config.headers['Authorization'] = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

const handleApiError = (error, navigate) => {
    if (error?.response?.status === 403) {
     //   navigate('/');
    } else {
        console.error("API Error:", error);
    }
};

function getQueryParams() {
    const searchParams = new URLSearchParams(window.location.search);
    const customer_id = searchParams.get('customer_id');
    const expires = searchParams.get('expires');
    const signature = searchParams.get('signature');

    let storedParams = {};
    if (customer_id && expires && signature) {
        storedParams = { customer_id, expires, signature };
        localStorage.setItem('queryParams', JSON.stringify(storedParams));
    } else {
        const saved = localStorage.getItem('queryParams');
        if (saved) {
            storedParams = JSON.parse(saved);
        }
    }

    return storedParams;
}

export const getProducts = async (filters = {}) => {
    const queryParams = getQueryParams();
    try {
        const response = await axios.get(`${API_URL}/products`, {
            params:  { ...queryParams, ...filters }
        });
        return response.data;
    } catch (error) {
        throw error;
    }
};


export const submitOrder = async (item) => {

    console.log('items',item);
    try {
        const response = await axios.post(`${API_URL}/order`, {
            id : item.subscription,
            email : item.email,
            gateway_type:item.paymentMethod
        });

        return {
            success: response.status === 201,
            data: response.data
        };
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.messages || ['Unknown error']
        };
    }
};

export const createPayment = async (item, comment = null) => {

    try {
        const response = await axios.post(`${API_URL}/payment`, {
            order_id : item.id,
            comment: comment
        });

        return {
            success: response.status === 201,
            data: response.data
        };
    } catch (error) {
        handleApiError(error, navigate);
        return {
            success: false,
            error: error.response?.data?.messages || ['Unknown error']
        };
    }
};

export const checkPayment = async (token) => {

    try {
        const response = await axios.post(`${API_URL}/payment/check`, {
            token : token,
        });
        return {
            success: response.status === 201,
            data: response.data
        };
    } catch (error) {
        return {
            success: false,
            error: error.response?.data?.messages || ['Unknown error']
        };
    }
};

export const checkEmailExists = async (email, navigate) => {
    const queryParams = getQueryParams();
    try {
        const response = await axios.get(`${API_URL}/check-user`, {
            params:{email:email }
        });
        console.log('response',response);
        if (response.data?.exists) {
            return true
        }else {
            return false;
        }
    } catch (error) {

    }
};

export const login = async (email,password) => {
    const queryParams = getQueryParams();
    const { customer_id, expires, signature } = queryParams;
    try {
        const response = await axios.post(`${API_URL}/login`, {
            email,
            password
        });
        if (response.data?.token) {
            localStorage.setItem('token', response.data.token);
            localStorage.setItem('data', JSON.stringify(response.data.user));
            return true;
        }
        return false;
    } catch (error) {
        return false;
    }
};

export const register = async (email,password) => {
    const queryParams = getQueryParams();
    const { customer_id, expires, signature } = queryParams;
    try {
        const response = await axios.post(`${API_URL}/register`, {
            email,password
        });
        if (response.data?.token) {
            localStorage.setItem('token', response.data.token);
            localStorage.setItem('data', JSON.stringify(response.data.user));

            return true;
        }
        return false;
    } catch (error) {
        console.log('error');
        return false;
    }
};

export const checkLogin = async (navigate) => {
    const queryParams = getQueryParams();
    const { customer_id, expires, signature } = queryParams;
    try {
        const response = await axios.post(`${API_URL}/login`, {
            customer_id,
            expires,
            signature
        });
        if (response.data?.token) {
            localStorage.setItem('token', response.data.token);
            localStorage.setItem('data', JSON.stringify(response.data.data));
        }
        return navigate('/products');
    } catch (error) {

    }
};


