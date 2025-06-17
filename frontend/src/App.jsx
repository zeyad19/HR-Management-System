
import React from 'react';
import { BrowserRouter } from 'react-router-dom';
import RouterComponent from './router'; // مفيش فولدر، فا بنستورد الملف مباشرة

function App() {
  return (
    <BrowserRouter>
      <RouterComponent />
    </BrowserRouter>
  );
}

export default App;