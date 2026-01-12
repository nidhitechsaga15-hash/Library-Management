import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, FlatList, ActivityIndicator, RefreshControl } from 'react-native';
import { apiService } from '../../../services/apiService';

export default function LibraryCardsScreen() {
  const [cards, setCards] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  useEffect(() => {
    loadCards();
  }, []);

  const loadCards = async () => {
    try {
      const data = await apiService.staff.getLibraryCards();
      setCards(data);
    } catch (error) {
      console.error('Error loading library cards:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = () => {
    setRefreshing(true);
    loadCards();
  };

  const renderCard = ({ item }) => (
    <View style={styles.card}>
      <Text style={styles.title}>Card: {item.card_number}</Text>
      <Text style={styles.subtitle}>User: {item.user?.name}</Text>
      <Text style={styles.date}>Valid until: {new Date(item.validity_date).toLocaleDateString()}</Text>
      <Text style={[styles.status, item.status === 'active' ? styles.active : styles.inactive]}>
        {item.status}
      </Text>
    </View>
  );

  if (loading) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#48bb78" />
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Library Cards</Text>
      </View>
      <FlatList
        data={cards}
        renderItem={renderCard}
        keyExtractor={(item) => item.id.toString()}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#f5f5f5' },
  centerContainer: { flex: 1, justifyContent: 'center', alignItems: 'center' },
  header: { backgroundColor: '#48bb78', padding: 20, paddingTop: 50 },
  headerTitle: { color: '#fff', fontSize: 24, fontWeight: 'bold' },
  card: { backgroundColor: '#fff', padding: 15, margin: 15, borderRadius: 12 },
  title: { fontSize: 18, fontWeight: 'bold', marginBottom: 5 },
  subtitle: { fontSize: 14, color: '#666', marginBottom: 5 },
  date: { fontSize: 12, color: '#999', marginBottom: 10 },
  status: { padding: 5, borderRadius: 5, alignSelf: 'flex-start' },
  active: { backgroundColor: '#d4edda', color: '#155724' },
  inactive: { backgroundColor: '#f8d7da', color: '#721c24' },
});

