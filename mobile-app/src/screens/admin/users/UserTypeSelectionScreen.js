import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';

export default function UserTypeSelectionScreen({ navigation }) {
  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()}>
          <Ionicons name="arrow-back" size={24} color="#fff" />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Select User Type</Text>
        <View style={{ width: 24 }} />
      </View>

      <View style={styles.content}>
        <TouchableOpacity
          style={styles.typeCard}
          onPress={() => navigation.navigate('StudentsList')}
        >
          <View style={styles.iconContainer}>
            <Ionicons name="school" size={64} color="#667eea" />
          </View>
          <Text style={styles.typeTitle}>Students</Text>
          <Text style={styles.typeDescription}>View and manage all students</Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={[styles.typeCard, styles.staffCard]}
          onPress={() => navigation.navigate('StaffList')}
        >
          <View style={[styles.iconContainer, styles.staffIconContainer]}>
            <Ionicons name="people" size={64} color="#48bb78" />
          </View>
          <Text style={styles.typeTitle}>Staff</Text>
          <Text style={styles.typeDescription}>View and manage all staff members</Text>
        </TouchableOpacity>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  header: {
    backgroundColor: '#667eea',
    padding: 20,
    paddingTop: 50,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  headerTitle: {
    color: '#fff',
    fontSize: 20,
    fontWeight: 'bold',
  },
  content: {
    flex: 1,
    padding: 20,
    justifyContent: 'center',
    gap: 20,
  },
  typeCard: {
    backgroundColor: '#fff',
    borderRadius: 16,
    padding: 40,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  staffCard: {
    marginTop: 0,
  },
  iconContainer: {
    width: 120,
    height: 120,
    borderRadius: 60,
    backgroundColor: '#f0f4ff',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 20,
  },
  staffIconContainer: {
    backgroundColor: '#f0f9f4',
  },
  typeTitle: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 10,
  },
  typeDescription: {
    fontSize: 14,
    color: '#666',
    textAlign: 'center',
  },
});

