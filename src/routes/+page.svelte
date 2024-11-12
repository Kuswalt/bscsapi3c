<script lang="ts">
  import { onMount } from 'svelte';

  type Student = {
    studno: string;
    fname: string;
    mname: string;
    lname: string;
    email: string;
    birthdate: string;
  };

  let students: Student[] = [];
  let editingStudent: Student | null = null;
  let isEditing = false;
  let searchQuery = '';

  async function fetchStudents() {
    try {
      const response = await fetch('http://localhost/bscsapi3c/api/routes.php?request=getstudents', {
        method: 'GET',
        headers: {
          'Accept': 'application/json'
        }
      });
      const data = await response.json();
      students = data;
    } catch (error) {
      console.error('Error fetching students:', error);
    }
  }

  async function deleteStudent(studno: string) {
    if (confirm('Are you sure you want to delete this student?')) {
      try {
        const response = await fetch(`http://localhost/bscsapi3c/api/routes.php?request=deletestudent/${studno}`, {
          method: 'POST'
        });
        if (response.ok) {
          await fetchStudents();
        }
      } catch (error) {
        console.error('Error deleting student:', error);
      }
    }
  }

  async function updateStudent() {
    if (!editingStudent) return;

    try {
      const payload = {
        studno: editingStudent.studno,
        fname: editingStudent.fname,
        mname: editingStudent.mname,
        lname: editingStudent.lname,
        email: editingStudent.email,
        birthdate: editingStudent.birthdate
      };
      
      // Encrypt the payload similar to the PHP encryptData function
      const iv = crypto.getRandomValues(new Uint8Array(16));
      const encoder = new TextEncoder();
      const data = encoder.encode(JSON.stringify(payload));
      
      const key = await crypto.subtle.importKey(
        'raw',
        encoder.encode('SampleKey'.padEnd(32, '\0')),
        { name: 'AES-CBC', length: 256 },
        false,
        ['encrypt']
      );
      
      const encryptedData = await crypto.subtle.encrypt(
        { name: 'AES-CBC', iv },
        key,
        data
      );

      const encryptedPayload = {
        data: btoa(String.fromCharCode(...new Uint8Array(encryptedData))),
        iv: btoa(Array.from(iv).map(b => ('00' + b.toString(16)).slice(-2)).join(''))
      };

      const response = await fetch('http://localhost/bscsapi3c/api/routes.php?request=updatestudent', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: btoa(JSON.stringify(encryptedPayload))
      });

      if (response.ok) {
        isEditing = false;
        editingStudent = null;
        await fetchStudents();
      }
    } catch (error) {
      console.error('Error updating student:', error);
    }
  }

  function startEditing(student: Student) {
    editingStudent = { ...student };
    isEditing = true;
  }

  function searchStudents(students: Student[], query: string): Student[] {
    const lowercaseQuery = query.toLowerCase().trim();
    if (!lowercaseQuery) return students;
    
    const searchTerms = lowercaseQuery.split(' ');
    
    return students.filter(student => {
      const fullName = `${student.fname} ${student.mname} ${student.lname}`.toLowerCase();
      return searchTerms.every(term => fullName.includes(term));
    });
  }

  onMount(() => {
    fetchStudents();
  });
</script>

<div class="container mx-auto p-4">
  <div class="mb-4">
    <input
      type="text"
      bind:value={searchQuery}
      placeholder="Search students..."
      class="px-4 py-2 border border-gray-300 rounded-md w-full max-w-md"
    />
  </div>
  <table class="min-w-full bg-white border border-gray-300">
    <thead>
      <tr class="bg-gray-100">
        <th class="px-6 py-3 border-b">First Name</th>
        <th class="px-6 py-3 border-b">Middle Name</th>
        <th class="px-6 py-3 border-b">Last Name</th>
        <th class="px-6 py-3 border-b">Email</th>
        <th class="px-6 py-3 border-b">Actions</th>
      </tr>
    </thead>
    <tbody>
      {#each searchStudents(students, searchQuery) as student}
        <tr class="hover:bg-gray-50">
          <td class="px-6 py-4 border-b">{student.fname}</td>
          <td class="px-6 py-4 border-b">{student.mname}</td>
          <td class="px-6 py-4 border-b">{student.lname}</td>
          <td class="px-6 py-4 border-b">{student.email}</td>
          <td class="px-6 py-4 border-b">
            <button
              class="bg-blue-500 text-white px-3 py-1 rounded mr-2"
              on:click={() => startEditing(student)}
            >
              Edit
            </button>
            <button
              class="bg-red-500 text-white px-3 py-1 rounded"
              on:click={() => deleteStudent(student.studno)}
            >
              Delete
            </button>
          </td>
        </tr>
      {/each}
    </tbody>
  </table>
</div>

{#if isEditing && editingStudent}
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg w-96">
      <h2 class="text-xl mb-4">Edit Student</h2>
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">First Name</label>
          <input
            type="text"
            bind:value={editingStudent.fname}
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Middle Name</label>
          <input
            type="text"
            bind:value={editingStudent.mname}
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Last Name</label>
          <input
            type="text"
            bind:value={editingStudent.lname}
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Email</label>
          <input
            type="email"
            bind:value={editingStudent.email}
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
          />
        </div>
        <div class="flex justify-end space-x-2">
          <button
            class="bg-gray-500 text-white px-4 py-2 rounded"
            on:click={() => {
              isEditing = false;
              editingStudent = null;
            }}
          >
            Cancel
          </button>
          <button
            class="bg-blue-500 text-white px-4 py-2 rounded"
            on:click={updateStudent}
          >
            Save
          </button>
        </div>
      </div>
    </div>
  </div>
{/if}

