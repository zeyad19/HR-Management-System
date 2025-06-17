
import React from 'react';
import { Routes, Route } from 'react-router-dom';

import Login from './pages/Login/Login';
import Payroll from './pages/Payroll/Payroll';

function RouterComponent() {
  return (
    <Routes>
      <Route path="/" element={<Login />} />
      <Route path="/payroll" element={<Payroll />} />
    </Routes>
  );
}

export default RouterComponent;
