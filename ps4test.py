#!C:\\Python38\\python.exe

DIR = "C:\\wamp64\\www\\ProjetS4\\"

from sklearn.metrics.pairwise import cosine_similarity
from IPython.display import display
import matplotlib.pyplot as plt
import pandas as pd
import statistics
import operator
import subprocess
import php
import popen2
import simplejson as json
import lib

#pd.set_option('display.max_columns', None)

events = pd.read_csv(DIR + 'evenements.csv')
participations = pd.read_csv(DIR + 'participation.csv')

participations_per_user = participations.groupby('user_id')['participation'].count()
participations_per_event = participations.groupby('event_id')['participation'].count()

participations_per_event_df = pd.DataFrame(participations_per_event)
filtered_participations_per_event_df = participations_per_event_df[participations_per_event_df.participation >= 1]
popular_event = filtered_participations_per_event_df.index.tolist()

participations_per_user_df = pd.DataFrame(participations_per_user)
filtered_participations_per_user_df = participations_per_user_df[participations_per_user_df.participation >= 1]
prolific_users = filtered_participations_per_user_df.index.tolist()

filtered_participations = participations[participations.event_id.isin(popular_event)]
filtered_participations = participations[participations.user_id.isin(prolific_users)]

participation_matrix = filtered_participations.pivot_table(index='user_id', columns='event_id', values='participation')
participation_matrix = participation_matrix.fillna(0)


def similar_users(user_id, matrix, k=3):
    # create a matrix of just the current user
    user = matrix[matrix.index == user_id]
    
    # and a matrix of all other users
    other_users = matrix[matrix.index != user_id]
    
    # calc cosine similarity between user and each other user
    similarities = cosine_similarity(user,other_users)[0].tolist()
    
    # create list of indices of these users
    indices = other_users.index.tolist()
    
    # create key/values pairs of user index and their similarity
    index_similarity = dict(zip(indices, similarities))
    
    # sort by similarity
    index_similarity_sorted = sorted(index_similarity.items(), key=operator.itemgetter(1))
    index_similarity_sorted.reverse()
    
    # grab k users off the top
    top_users_similarities = index_similarity_sorted[:k]
    users = [u[0] for u in top_users_similarities]
    
    return users

with open('curr_user.txt', 'r') as f:
    curr_user = int(f.read())

similar_user_indices = similar_users(curr_user, participation_matrix)

print(similar_user_indices)

def recommend_item(user_index, similar_user_indices, matrix, items=5):
    
    # load vectors for similar users
    similar_users = matrix[matrix.index.isin(similar_user_indices)]
    # calc avg participations across the similar users
    similar_users = similar_users.mean(axis=0)
    # convert to dataframe so its easy to sort and filter
    similar_users_df = pd.DataFrame(similar_users, columns=['mean'])
    
    
    # load vector for the current user
    user_df = matrix[matrix.index == user_index]
    # transpose it so its easier to filter
    user_df_transposed = user_df.transpose()
    # rename the column as 'participation'
    user_df_transposed.columns = ['participation']
    # remove any rows without a 0 value. event not watched yet
    user_df_transposed = user_df_transposed[user_df_transposed['participation']==0]
    # generate a list of events the user has not seen
    events_unseen = user_df_transposed.index.tolist()
    
    # filter avg participations of similar users for only event the current user has not seen
    similar_users_df_filtered = similar_users_df[similar_users_df.index.isin(events_unseen)]
    # order the dataframe
    similar_users_df_ordered = similar_users_df.sort_values(by=['mean'], ascending=False)
    # grab the top n event   
    top_n_event = similar_users_df_ordered.head(20)
    top_n_event_indices = top_n_event.index.tolist()
    # lookup these event in the other dataframe to find names
    event_information = events[events['event_id'].isin(top_n_event_indices)]
    
    return event_information

display(recommend_item(curr_user, similar_user_indices, participation_matrix))

ri = recommend_item(curr_user, similar_user_indices, participation_matrix).event_id

ri.to_csv("data.csv")