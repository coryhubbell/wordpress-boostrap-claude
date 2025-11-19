/**
 * Main Application Component
 * WordPress Bootstrap Claude - Visual Interface
 */

import { useEffect } from 'react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import SideBySideEditor from '@components/SideBySideEditor';
import { useEditorStore } from '@/store/editorStore';

// Create React Query client
const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      refetchOnWindowFocus: false,
      retry: 1,
    },
  },
});

function App() {
  // Always use light mode
  useEffect(() => {
    document.documentElement.classList.remove('dark');
  }, []);

  return (
    <QueryClientProvider client={queryClient}>
      <div className="app">
        <SideBySideEditor />
      </div>
    </QueryClientProvider>
  );
}

export default App;
