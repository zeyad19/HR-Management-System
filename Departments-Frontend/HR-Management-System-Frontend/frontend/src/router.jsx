import React from 'react';
import { Routes, Route } from 'react-router-dom';
import Sidebar from './components/Sidebar';
import Holidayes from './pages/Holidayes/Holidayes';
import Login from './pages/Login/Login';
import HolidayForm from './pages/Holidayes/HolidayForm';
import Payroll from './pages/Payroll/Payroll';

import Departments from './pages/Departments/Departments';
import CreateDepartment from './pages/Departments/CreateDepartment';
import EditDepartment from './pages/Departments/EditDepartment';


export default function RouterComponent() {
  return (
    <Routes>
      <Route path="/" element={<Sidebar />}>
        <Route index element={<Holidayes/>} />
        <Route path="holidays" element={<Holidayes />} />
        <Route path="create" element={<HolidayForm />} />
        <Route path="holidays/:id/" element={<HolidayForm isEdit />} />
        <Route path="payroll" element={<Payroll />} />
        <Route path="/departments" element={<Departments />} />
        <Route path="/departments/create" element={<CreateDepartment />} />
        <Route path="/departments/:id" element={<EditDepartment />} />
      </Route>
      <Route path="/login" element={<Login />} />
    </Routes>
  );
}
