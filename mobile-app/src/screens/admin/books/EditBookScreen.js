import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TextInput,
  TouchableOpacity,
  Alert,
  ActivityIndicator,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { apiService } from '../../../services/apiService';
import SearchableDropdown from '../../../components/SearchableDropdown';

export default function EditBookScreen({ navigation, route }) {
  const { bookId } = route.params;
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [authors, setAuthors] = useState([]);
  const [categories, setCategories] = useState([]);
  const [formData, setFormData] = useState({
    isbn: '',
    title: '',
    author_id: '',
    category_id: '',
    publisher: '',
    edition: '',
    publication_year: '',
    total_copies: '',
    available_copies: '',
    rack_number: '',
    language: '',
    pages: '',
    status: 'available',
    description: '',
  });

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      const [book, authorsData, categoriesData] = await Promise.all([
        apiService.admin.getBook(bookId),
        apiService.admin.getAuthors().catch(() => []),
        apiService.admin.getCategories().catch(() => []),
      ]);

      if (authorsData && authorsData.length > 0) {
        setAuthors(authorsData.map(a => ({ label: a.name || 'Unknown', value: a.id })));
      } else {
        setAuthors([]);
      }
      
      if (categoriesData && categoriesData.length > 0) {
        setCategories(categoriesData.map(c => ({ label: c.name || 'Unknown', value: c.id })));
      } else {
        setCategories([]);
      }

      setFormData({
        isbn: book.isbn || '',
        title: book.title || '',
        author_id: book.author_id?.toString() || '',
        category_id: book.category_id?.toString() || '',
        publisher: book.publisher || '',
        edition: book.edition || '',
        publication_year: book.publication_year?.toString() || '',
        total_copies: book.total_copies?.toString() || '',
        available_copies: book.available_copies?.toString() || '',
        rack_number: book.rack_number || '',
        language: book.language || '',
        pages: book.pages?.toString() || '',
        status: book.status || 'available',
        description: book.description || '',
      });
    } catch (error) {
      console.error('Error loading book data:', error);
      Alert.alert('Error', 'Failed to load book data');
      navigation.goBack();
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async () => {
    if (!formData.isbn || !formData.title || !formData.author_id || !formData.category_id || !formData.total_copies) {
      Alert.alert('Error', 'Please fill all required fields');
      return;
    }

    setSaving(true);
    try {
      const submitData = {
        isbn: formData.isbn.trim(),
        title: formData.title.trim(),
        author_id: parseInt(formData.author_id),
        category_id: parseInt(formData.category_id),
        total_copies: parseInt(formData.total_copies),
        available_copies: parseInt(formData.available_copies || formData.total_copies),
        status: formData.status,
        publisher: formData.publisher?.trim() || null,
        edition: formData.edition?.trim() || null,
        publication_year: formData.publication_year ? parseInt(formData.publication_year) : null,
        rack_number: formData.rack_number?.trim() || null,
        language: formData.language?.trim() || null,
        pages: formData.pages ? parseInt(formData.pages) : null,
        description: formData.description?.trim() || null,
      };

      await apiService.admin.updateBook(bookId, submitData);
      Alert.alert('Success', 'Book updated successfully', [
        { text: 'OK', onPress: () => navigation.goBack() },
      ]);
    } catch (error) {
      console.error('Update book error:', error);
      const errorMessage = error.response?.data?.message || error.message || 'Failed to update book';
      Alert.alert('Error', errorMessage);
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#667eea" />
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()}>
          <Ionicons name="arrow-back" size={24} color="#fff" />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Edit Book</Text>
        <View style={{ width: 24 }} />
      </View>

      <ScrollView style={styles.content}>
        <View style={styles.formGroup}>
          <Text style={styles.label}>ISBN *</Text>
          <TextInput
            style={styles.input}
            placeholder="Enter ISBN"
            value={formData.isbn}
            onChangeText={(text) => setFormData({ ...formData, isbn: text })}
          />
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>Title *</Text>
          <TextInput
            style={styles.input}
            placeholder="Enter book title"
            value={formData.title}
            onChangeText={(text) => setFormData({ ...formData, title: text })}
          />
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>Author *</Text>
          <SearchableDropdown
            options={authors}
            selectedValue={formData.author_id}
            onSelect={(value) => setFormData({ ...formData, author_id: value })}
            placeholder="Select author"
          />
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>Category *</Text>
          <SearchableDropdown
            options={categories}
            selectedValue={formData.category_id}
            onSelect={(value) => setFormData({ ...formData, category_id: value })}
            placeholder="Select category"
          />
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>Publisher</Text>
          <TextInput
            style={styles.input}
            placeholder="Enter publisher"
            value={formData.publisher}
            onChangeText={(text) => setFormData({ ...formData, publisher: text })}
          />
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>Edition</Text>
          <TextInput
            style={styles.input}
            placeholder="e.g., 1st, 2nd, 3rd"
            value={formData.edition}
            onChangeText={(text) => setFormData({ ...formData, edition: text })}
          />
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>Publication Year</Text>
          <TextInput
            style={styles.input}
            placeholder="e.g., 2024"
            value={formData.publication_year}
            onChangeText={(text) => setFormData({ ...formData, publication_year: text })}
            keyboardType="numeric"
          />
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>Total Copies *</Text>
          <TextInput
            style={styles.input}
            placeholder="Enter total copies"
            value={formData.total_copies}
            onChangeText={(text) => setFormData({ ...formData, total_copies: text })}
            keyboardType="numeric"
          />
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>Available Copies</Text>
          <TextInput
            style={styles.input}
            placeholder="Enter available copies"
            value={formData.available_copies}
            onChangeText={(text) => setFormData({ ...formData, available_copies: text })}
            keyboardType="numeric"
          />
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>Rack Number</Text>
          <TextInput
            style={styles.input}
            placeholder="Enter rack number"
            value={formData.rack_number}
            onChangeText={(text) => setFormData({ ...formData, rack_number: text })}
          />
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>Language</Text>
          <TextInput
            style={styles.input}
            placeholder="Enter language"
            value={formData.language}
            onChangeText={(text) => setFormData({ ...formData, language: text })}
          />
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>Pages</Text>
          <TextInput
            style={styles.input}
            placeholder="Enter number of pages"
            value={formData.pages}
            onChangeText={(text) => setFormData({ ...formData, pages: text })}
            keyboardType="numeric"
          />
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>Status *</Text>
          <SearchableDropdown
            options={[
              { label: 'Available', value: 'available' },
              { label: 'Unavailable', value: 'unavailable' },
            ]}
            selectedValue={formData.status}
            onSelect={(value) => setFormData({ ...formData, status: value })}
            placeholder="Select status"
          />
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>Description</Text>
          <TextInput
            style={[styles.input, styles.textArea]}
            placeholder="Enter book description"
            value={formData.description}
            onChangeText={(text) => setFormData({ ...formData, description: text })}
            multiline
            numberOfLines={4}
          />
        </View>

        <TouchableOpacity
          style={[styles.submitButton, saving && styles.submitButtonDisabled]}
          onPress={handleSubmit}
          disabled={saving}
        >
          {saving ? (
            <ActivityIndicator color="#fff" />
          ) : (
            <Text style={styles.submitButtonText}>Update Book</Text>
          )}
        </TouchableOpacity>
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  centerContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
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
  },
  formGroup: {
    marginBottom: 15,
  },
  label: {
    fontSize: 14,
    fontWeight: '600',
    color: '#333',
    marginBottom: 8,
  },
  input: {
    backgroundColor: '#fff',
    borderRadius: 8,
    padding: 12,
    fontSize: 16,
    borderWidth: 1,
    borderColor: '#e0e0e0',
  },
  textArea: {
    height: 100,
    textAlignVertical: 'top',
  },
  submitButton: {
    backgroundColor: '#667eea',
    padding: 15,
    borderRadius: 8,
    alignItems: 'center',
    marginTop: 20,
    marginBottom: 30,
  },
  submitButtonDisabled: {
    opacity: 0.6,
  },
  submitButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '600',
  },
});

