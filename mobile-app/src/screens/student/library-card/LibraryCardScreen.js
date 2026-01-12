import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, ActivityIndicator, TouchableOpacity } from 'react-native';
import { apiService } from '../../../services/apiService';

export default function LibraryCardScreen() {
  const [card, setCard] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadCard();
  }, []);

  const loadCard = async () => {
    try {
      const data = await apiService.student.getLibraryCard();
      setCard(data);
    } catch (error) {
      console.error('Error loading library card:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#4299e1" />
      </View>
    );
  }

  if (!card) {
    return (
      <View style={styles.container}>
        <View style={styles.header}>
          <Text style={styles.headerTitle}>Library Card</Text>
        </View>
        <View style={styles.content}>
          <Text style={styles.noCardText}>No library card found</Text>
          <TouchableOpacity style={styles.requestButton}>
            <Text style={styles.requestButtonText}>Request Card</Text>
          </TouchableOpacity>
        </View>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Library Card</Text>
      </View>
      <View style={styles.cardContainer}>
        <View style={styles.card}>
          <Text style={styles.cardNumber}>{card.card_number}</Text>
          <Text style={styles.validity}>Valid until: {new Date(card.validity_date).toLocaleDateString()}</Text>
          <Text style={[styles.status, card.status === 'active' ? styles.active : styles.inactive]}>
            {card.status}
          </Text>
        </View>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#f5f5f5' },
  centerContainer: { flex: 1, justifyContent: 'center', alignItems: 'center' },
  header: { backgroundColor: '#4299e1', padding: 20, paddingTop: 50 },
  headerTitle: { color: '#fff', fontSize: 24, fontWeight: 'bold' },
  content: { flex: 1, justifyContent: 'center', alignItems: 'center', padding: 20 },
  noCardText: { fontSize: 16, color: '#666', marginBottom: 20 },
  requestButton: { backgroundColor: '#4299e1', padding: 15, borderRadius: 8 },
  requestButtonText: { color: '#fff', fontSize: 16, fontWeight: '600' },
  cardContainer: { padding: 20 },
  card: { backgroundColor: '#fff', padding: 30, borderRadius: 12, alignItems: 'center', shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.1, shadowRadius: 4, elevation: 3 },
  cardNumber: { fontSize: 24, fontWeight: 'bold', marginBottom: 20 },
  validity: { fontSize: 14, color: '#666', marginBottom: 10 },
  status: { padding: 8, borderRadius: 8, marginTop: 10 },
  active: { backgroundColor: '#d4edda', color: '#155724' },
  inactive: { backgroundColor: '#f8d7da', color: '#721c24' },
});

