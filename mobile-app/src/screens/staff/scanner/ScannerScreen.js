import React from 'react';
import { View, Text, StyleSheet } from 'react-native';

export default function ScannerScreen() {
  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Barcode Scanner</Text>
      </View>
      <View style={styles.content}>
        <Text style={styles.infoText}>Scanner functionality will be implemented here</Text>
        <Text style={styles.infoText}>This requires camera permissions and barcode scanning library</Text>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#f5f5f5' },
  header: { backgroundColor: '#48bb78', padding: 20, paddingTop: 50 },
  headerTitle: { color: '#fff', fontSize: 24, fontWeight: 'bold' },
  content: { flex: 1, justifyContent: 'center', alignItems: 'center', padding: 20 },
  infoText: { fontSize: 16, color: '#666', textAlign: 'center', marginBottom: 10 },
});

