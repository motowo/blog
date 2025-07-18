import React from 'react';
import { render, screen } from '@testing-library/react';
import App from './App';

test('renders blog app header', () => {
  render(<App />);
  const headerElement = screen.getByText(/Blog App/i);
  expect(headerElement).toBeInTheDocument();
});