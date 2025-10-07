import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import HomePage from './pages/HomePage';
import ConfirmOrderPage from './pages/ConfirmOrderPage.jsx';
import NoAccessPage from './pages/NoAccessPage';
import ThankYouPage from './pages/ThankYouPage.jsx';
import B2bLogin from "./pages/b2bLogin.jsx";
import SelectSubscription from "./pages/SelectSubscription.jsx";


function App() {
    return (
        <Router>
            <Routes>
                <Route path="/" element={<SelectSubscription />} />
                <Route path="/products" element={<HomePage />} />
                <Route path="/no-access" element={<NoAccessPage />} />
                <Route path="/confirm-order" element={<ConfirmOrderPage />} />
                <Route path="/thank-you" element={<ThankYouPage />} />
            </Routes>
        </Router>
    );
}

export default App;
